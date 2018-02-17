<?php

/*
  |--------------------------------------------------------------------------
  | Application Routes
  |--------------------------------------------------------------------------
  |
  | Here is where you can register all of the routes for an application.
  | It's a breeze. Simply tell Laravel the URIs it should respond to
  | and give it the controller to call when that URI is requested.
  |
 */

// REACT NATIVE  RELEASE
// PLAC USER 
Route::resource('placuser/register', 'PlacUserController');
Route::get('placuser/profiles/get/{plac_user_id}', 'PlacUserController@getPlacUserProfiles');
Route::get('placuser/profiles/get2/{profile_from_id}/{plac_user_id}', 'PlacUserController@getPlacUserProfiles2');
/* --CONFIRM EMAIL--- */
Route::get('placuser/confirm/{email}/{token}', 'PlacUserController@confirmNewUser');
/* --UPDATE  TERMS --- */
Route::post('placuser/updateterms', 'PlacUserController@updateTerms');

// PLAC USER BLACK LIST
Route::resource('placuser/blacklist', 'PlacUserBlackListController');
Route::post('placuser/blacklist/manage', 'PlacUserBlackListController@manageBlackList');

//PROFILE
Route::post('placuser/profile/register', 'ProfileController@storeReact');
Route::post('placuser/profile/facebookfriends', 'ProfileController@getProfilesFacebookFriends');
Route::get('placuser/profile/data/{profile_from_id}/{profile_to_id}', 'ProfileController@getProfileData');
Route::get('placuser/profile/recents/{profile_from_id}', 'ProfileController@getRecentProfiles');
Route::get('placuser/profile/recents2/{profile_from_id}', 'ProfileController@getRecentProfiles2');
Route::get('placuser/profile/searched/{profile_from_id}/{value}', 'ProfileController@getProfilesSearched');
Route::post('placuser/profile/suggest', 'ProfileController@getSuggestProfilesWithFilters');
Route::resource('placuser/profile', 'ProfileController');



// BREEDS
Route::get('breed/filter/{petType}/{value}', 'BreedController@filterBreeds');

// PROFILE-DISMISS
Route::resource('placuser/profile/dismiss', 'ProfileDismissController');


//FOLLOWER
Route::get('placuser/profiles/followers/{profile_id}', 'FollowersController@getCountFollower');
Route::get('placuser/profiles/followers/getfollowers/{profileDevice}/{profileTo}', 'FollowersController@getFollowers');
Route::get('placuser/profiles/followers/getfollowings/{profileDevice}/{profileFrom}', 'FollowersController@getFollowings');
Route::post('placuser/profiles/followers/manage', 'FollowersController@manageFollower');

Route::resource('placuser/profiles/followers', 'FollowersController');


//POST
Route::resource('placuser/profile/post', 'PostController');
Route::get('placuser/profile/post/me/{profileid}', 'PostController@getProfilePostsWithImage');
Route::get('placuser/profile/post/me/{profile_from_id}/{profileid}', 'PostController@getProfilePosts');
Route::post('placuser/profile/post/me/following/', 'PostController@getFollowingPost');
Route::get('placuser/profile/post/{profileId}/{postId}', 'PostController@getPost');

//POST LIKES
Route::post('placuser/profile/post/like/manage', 'PostLikesController@managePostLike');
Route::get('placuser/profile/post/like/get/{post_id}', 'PostLikesController@getPostLikes');


//POST COMMENTS
Route::resource('placuser/profile/post/comments', 'PostCommentsController');
Route::get('placuser/profile/post/comments/get/{post_id}/{profile_from_id}', 'PostCommentsController@getPostComments');


//EVENTS 
Route::get('places/events/type/{type}/{profileFromId}', 'EventsController@getEventsByType');
Route::get('places/events/assistants/manage/{event_id}/{profileFromId}', 'EventAssistantsController@manageEventAssistance');



//PLACES
Route::get('places/{latitude}/{longitude}/{distance}', 'PlaceController@getPlaces');
/* -----deprecated----- */
Route::get('places/search/{filter}', 'PlaceController@getPlacesSearch');
/* -----new method for search----- */
Route::get('places/searched/filter/{value}/{city}', 'PlaceController@getPlacesSearched');
Route::post('places/filtered', 'PlaceController@getPlacesFiltered');
Route::get('places/init/around/{latitude}/{longitude}/{distance}', 'PlaceController@getInitPlacesAround');
Route::get('places/init/around/paginate/{latitude}/{longitude}/{distance}', 'PlaceController@getInitPlacesAroundPaginate');
// V2
Route::post('places/main', 'PlaceController@getMainPlacesByCity');
Route::post('places/city', 'PlaceController@getPlacesByCity');
Route::post('places/near/me', 'PlaceController@getPlacesNear');

// CITIES

Route::get('cities/search/{value}', 'CitiesController@searchCity');
Route::get('citiescol', ['middleware' => 'cors', function() {
        return \Response::json(\Plac\City::where("country_id", "COL")->get());
    }]);
    
    
Route::resource("cities", "CitiesController");

// CATEGORIES
Route::get("categories/sub/all", "CategoriesCompanyController@getSubcategories");


//-- PRODUCTS CONTROLLER NEW 
Route::post("store/products/main", "ProductsController@getProductsMain");
Route::get("store/products/categories/{productType}", "ProductsController@getCategories");
Route::post("store/products/user/views", "ProductsController@addViewToProduct");
Route::get("store/place/assessment/{place_id}", "PlaceController@getPlaceAssessment");


// 23/06/2017
// PLAC USER SHIPPING ADDRESS NEW
Route::resource('placuser/shipping/address', 'PlacUserShippingAddressController');
Route::get('placuser/shipping/address/update/{shipping_address_id}/{plac_user_id}', 'PlacUserShippingAddressController@updateShippingAddressMain');

// NOTIFICATION
Route::get("store/notifications", "StoreController@sendBuyNotification");

//NOTIFICATION POST 
Route::get("notifications/post/me/{profileTo}", "NotificationManagePostController@getNotifications");
Route::get("notifications/follower/me/{profileTo}", "FollowersController@getRecentsFollowers");


// ASSESSMENT
Route::resource("store/order/assessment", "AssessmentController");


//ADVERTISING

Route::post("advetisements", "AdvertisementsUsersController@getAllAdvertisements");

//ADVERTISING LIKES
Route::post('place/advertisement/like/manage', 'AdvertisementsLikesController@manageAdvertisementLikes');
Route::post("place/advertisement/user/save", "AdvertisementsUsersController@saveNewRegisterAdvertising");

//ADVERTISING COMMENTS
Route::resource('place/advertisement/comments', 'AdvertisementsCommentsController');
Route::get('place/advertisement/comments/get/{advertisement_id}/', 'AdvertisementsCommentsController@getAdvertisementComments');


//COMPLAINTS 
Route::resource('post/complaints', 'ComplaintControllers');
Route::post('post/report', 'ComplaintControllers@postReport');
//TEST EMAIL
//update DATA CONTROLELR
Route::post('placuser/update', 'UpdateDataController@updateInstallation');



//--ORDERS--
//2017/10/18
Route::resource("orders", "OrdersController");
Route::get("store/orders/user/{plac_user_id}/{state}", "OrdersController@getOrdersUser");
/*23/10/2017 UPDATE  ORDER MERCADO PAGO AND NOIFITY TO SELLER*/
//Update order state orders created 2 minutes ago and notified pending payment 
Route::get("orders/mercadopago/update/created", "OrdersController@updateOrderStateCreatedAt");

//Update order state and change state 
Route::get("orders/mercadopago/update/lastmodified", "OrdersController@updateOrderStateLastModified");
//Update order state  and change state  to approved
Route::get("orders/mercadopago/update/approved", "OrdersController@updateOrderStateLastApproved");
//TEMPLATE EMAIL 
Route::get("store/order/template", "OrdersController@getTemplate");



// PRODUCT  STORE
//12/10/2017
Route::post("store/products/main2", "ProductsController@getProductsMain2");
//07/11/2017
Route::get("store/product/{productId}", "ProductsController@getProduct");


//10/31/2017 PRODUCT QUESTION
Route::resource("store/product/questions", "ProductsQuestionsController");
Route::get("store/product/question/{product_id}", "ProductsQuestionsController@getQuestionsByProduct");
Route::get("store/product/questions/user/{plac_user_id}", "ProductsQuestionsController@getQuestionsByUser");


//12/12/2017 PLAC USER NOTIFICATION SETTINGS
Route::put("placuser/configuration/notifications/update/{plac_user_id}", "PlacUserNotificationsSettingsController@updateNotificationStateByPlacUserId");
Route::get("placuser/configuration/notifications/{plac_user_id}", "PlacUserNotificationsSettingsController@getNotificationsSettingsByPlacUserId");


// MARKET PLAC APP

Route::post("store/places/city", "StoreController@getStoresByCity");
Route::post("store/place/products", "ProductsController@getProductsByStore");








