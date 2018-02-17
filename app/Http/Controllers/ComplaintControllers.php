<?php

namespace Plac\Http\Controllers;

use Illuminate\Http\Request;
use Plac\Http\Requests;
use Plac\Helpers\HelperIDs;
use Mail;

class ComplaintControllers extends Controller {

    /**
     * Display a listing of the resource.
     *
     * @return \Illuminate\Http\Response
     */
    public function index() {
        return \Plac\Complaint::all();
    }

    /**
     * Show the form for creating a new resource.
     *
     * @return \Illuminate\Http\Response
     */
    public function create() {
        //
    }

    /**
     * Store a newly created resource in storage.
     *
     * @param  \Illuminate\Http\Request  $request
     * @return \Illuminate\Http\Response
     */
    public function store(Request $request) {
        //
    }

    /**
     * Display the specified resource.
     *
     * @param  int  $id
     * @return \Illuminate\Http\Response
     */
    public function show($id) {
        //
    }

    /**
     * Show the form for editing the specified resource.
     *
     * @param  int  $id
     * @return \Illuminate\Http\Response
     */
    public function edit($id) {
        //
    }

    /**
     * Update the specified resource in storage.
     *
     * @param  \Illuminate\Http\Request  $request
     * @param  int  $id
     * @return \Illuminate\Http\Response
     */
    public function update(Request $request, $id) {
        //
    }

    /**
     * Remove the specified resource from storage.
     *
     * @param  int  $id
     * @return \Illuminate\Http\Response
     */
    public function destroy($id) {
        //
    }

    public function postReport(Request $request) {

        $complaint = $request['complaint'];
        $complaintId = $complaint['complaint_id'];
        $placUser = $request['plac_user'];
        $placUserId = $placUser['plac_user_id'];

        $post = $request['post'];
        $postId = $post['post_id'];

        $postReportId = $this->generateUniqueId();

        $postReport = new \Plac\PostReports();
        $postReport->post_report_id = $postReportId;
        $postReport->plac_user_id = $placUserId;
        $postReport->post_id = $postId;
        $postReport->complaint_id = $complaintId;
        $postReport->save();
        $placUser = $post['profile']['plac_user'];
        try {
            $this->sendMessage('no-reply@placapp.com', $placUser['plac_user_email'], $placUser, $complaint['complaint_name'], "Tu publicación ha sido reportada porque infringe con  nuestros términos de uso, el motivo por el que  fue reportada es el siguiente:");
            $this->sendMessage('no-reply@placapp.com', 'complaints@placapp.com', $placUser, $complaint['complaint_name'], 'Han reportado la  publicación: ' . $post['post_id'] . " por el siguiente motivo:");
        } catch (\Exception $e) {
            return $e->getMessage();
        }


        return "post_reported";
    }

    public function sendMessage($from, $to, $placUser, $complaintName, $message) {


        Mail::send('emails.complaints.post.index', ['msg' => $message, 'user' => $placUser['plac_user_name'], 'complaintName' => $complaintName], function ($m) use ($from, $to) {
            $m->from($from, 'Equipo de PLAC');
            $m->to($to)->subject('Publicación reportada');
        });
    }

    public function generateUniqueId() {

        $idGenerated = HelperIDs::generateID();
        $count_exist_id = \Plac\PostReports::where('post_report_id', $idGenerated)->count();

        if ($count_exist_id == 1) {
            $this->generateUniqueId();
        } else {
            return $idGenerated;
        }
    }

}
