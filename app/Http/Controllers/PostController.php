<?php

namespace Plac\Http\Controllers;

use DateTime;
use Illuminate\Http\Request;
use Plac\Follower;
use Plac\Helpers\HelperIDs;
use Plac\Helpers\JsonObjects;
use Plac\Http\Requests;
use Plac\Post;
use Plac\PostComments;
use Plac\PostLikes;

class PostController extends Controller {

    /**
     * Store a newly created resource in storage.
     *
     * @param  \Illuminate\Http\Request $request
     * @return \Illuminate\Http\Response
     */
    public function store(Request $request) {
        $profileId = $request["profile_id"];
        $message = $request['post_message'];
        $postImage = $request['post_image'];
        $post = new Post();
        $postId = $this->generateUniqueId();
        if ($postImage != null || $message != null) {
            if ($message != null) {
                $post->message = $message;
            }
            if ($postImage != null) {
                $pathCloudinary = "profiles/post/post";
                $pathImage = ImageController::base64_to_jpeg($postImage, public_path('images/profile/post/' . $profileId . "name.jpg"));
                $imagePathCloudinary = ImageController::saveImageOnCloudinary($pathImage, $postId, $pathCloudinary);
                unlink($pathImage);
                $post->post_path_image = $imagePathCloudinary;
            }
        } else {
            return "Campos vacios";
        }

        $post->post_id = $postId;
        $post->profile_id = $profileId;
        $post->save();

        $posts = Post::where("post_id", $postId)->with("profile.placUser", "profile.breed", 'commentsCount', "likesCount")->get();
        $posts = $this->getPostLikesComments($posts, $profileId);
        $posts[0]["post_time_ago"] = "Hace un momento";
        $posts = $this->getPetType($posts);
        return JsonObjects::createJsonObjectModel("post_created", $postId, $posts[0]);
    }

    public function getProfilePostsWithImage($profileId) {
        $posts = Post::where("profile_id", $profileId)->where("post_state", "ACTIVE")->whereNotNull('post_path_image')->with("profile.placUser", "profile.breed")->with('commentsCount', "likesCount")->orderBy('created_at', 'desc')->paginate(20);
        $posts = $this->getPostLikesComments($posts, $profileId);
        $posts = $this->getPostTimeAgoCreatedAt($posts);
        $posts = $this->getPetType($posts);
        return $posts;
    }

    public function getProfilePosts($profileFromId, $profileId) {

        $posts = Post::where("profile_id", $profileId)->where("post_state", "ACTIVE")->whereNotNull('post_path_image')->with("profile.placUser", "profile.breed")->with('commentsCount', "likesCount")->orderBy('created_at', 'desc')->paginate(21);
        $posts = $this->getPostLikesComments($posts, $profileFromId);
        $posts = $this->getPostTimeAgoCreatedAt($posts);
        $posts = ProfileController::getCategoriesNamePost($posts);
        $posts = $this->getPetType($posts);
        return $posts;
    }

    public function getFollowingPost(Request $request) {

        $profile = $request->profile;

        $profileId = $profile["profile_id"];
        $profiles = Follower::where("profile_from_id", $profileId)->pluck('profile_to_id');
        $posts = Post::where("post_state", "ACTIVE")->whereIn("profile_id", $profiles)->with("profile.placUser", "profile.breed", 'commentsCount', "likesCount")->orderBy('created_at', 'desc')->paginate(20);
        $posts = ProfileController::getCategoriesNamePost($posts);
        $posts = $this->getPostLikesComments($posts, $profileId);
        $posts = $this->getPostTimeAgoCreatedAt($posts);
        $posts = $this->getPetType($posts);
        $posts = json_encode($posts);
        $posts = json_decode($posts, true);
        if ($posts["current_page"] == 1) {
            $posts = $this->getAdvertisement(0, $posts, $profile);
        } else {
            $skip = $posts["current_page"] * 3;
            $posts = $this->getAdvertisement($skip, $posts, $profile);
        }

        return $posts;
    }

    /**
     * Display the specified resource.
     *
     * @param  int $id
     * @return \Illuminate\Http\Response
     */
    public function show($id) {

        $post = Post::where("post_id", $id)->where("post_state", "ACTIVE")->with("profile", 'commentsCount', "likesCount")->first();
        $postLikesCount = PostLikes::where("post_id", $post->post_id)->where("profile_id", $post->profile_id)->count();
        $postCommentsCount = PostComments::where("post_id", $post->post_id)->where("profile_id", $post->profile_id)->count();
        $post['has_Liked'] = $postLikesCount;
        $post['has_commented'] = $postCommentsCount;
        return $post;
    }

    public function getPost($profileId, $postId) {
        $posts = Post::where("post_id", $postId)->with("profile.placUser", "profile.breed", 'commentsCount', "likesCount")->get();
        $posts = $this->getPostLikesComments($posts, $profileId);
        $posts = $this->getPostTimeAgoCreatedAt($posts);
        $posts = $this->getPetType($posts);

        return $posts;
    }

    /**
     * Update the specified resource in storage.
     *
     * @param  \Illuminate\Http\Request $request
     * @param  int $id
     * @return \Illuminate\Http\Response
     */
    public function update(Request $request, $id) {
        $message = $request->message;
        $post = Post::find($id);
        $post->message = $message;
        $post->save();
        return "post_udpated";
    }

    /**
     * Remove the specified resource from storage.
     *
     * @param  int $id
     * @return \Illuminate\Http\Response
     */
    public function destroy($id) {

        $post = Post::find($id);
        $post->post_state = "DELETED";
        $post->save();
        return "post_deleted";
    }

    public function generateUniqueId() {
        $idGenerated = HelperIDs::generateID();
        $count = Post::where('post_id', $idGenerated)->count();
        if ($count == 1) {
            $this->generateUniqueId();
        } else {
            return $idGenerated;
        }
    }

    public function getPostTimeAgoCreatedAt($posts) {
        $i = 0;
        foreach ($posts as $post) {
            $created_at = $post->created_at;
            $now = new DateTime();
            $ago = \Plac\Helpers\DateUtils::getTimeAgo($now, $created_at);

            $posts[$i]["post_time_ago"] = $ago;
            $i++;
        }
        return $posts;
    }

    public function getPostLikesComments($posts, $profileId) {
        $i = 0;
        foreach ($posts as $post) {
            $postLikesCount = PostLikes::where("post_id", $post->post_id)->where("profile_id", $profileId)->count();
            $postCommentsCount = PostComments::where("post_id", $post->post_id)->where("profile_id", $profileId)->count();

            $posts[$i]["has_Liked"] = $postLikesCount;
            if ($postCommentsCount > 0) {
                $postCommentsCount = 1;
            }
            $posts[$i]["has_commented"] = $postCommentsCount;
            $i++;
        }

        return $posts;
    }

    public function getPetType($posts) {


        $profiles = new ProfileController();
        for ($i = 0; $i < count($posts); $i++) {
            $profile = $posts[$i]->profile;
            if ($profile->profile_type == "pet") {
                $petType = $profiles->getPetType($profile->pet_type);
                $posts[$i]["profile"]["pet_type_translate"] = $petType;
            }
        }

        return $posts;
    }

    public function getAdvertisement($offset, $posts, $profile) {
        $ads = AdvertisementsUsersController::getAllAdvertisements($offset, $profile);
        if ($ads != null) {
            $position = 5;
            $i = 0;
            while ($position <= count($posts["data"])) {
                if (count($ads) > $i) {
                    array_splice($posts["data"], $position, 0, json_encode($ads[$i], JSON_FORCE_OBJECT));
                }
                $position = $position + 6;
                $i++;
            }
        }
        return $posts;
    }

}
