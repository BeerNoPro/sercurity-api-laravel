<?php

namespace App\Http\Controllers\Sercurity;

use App\Http\Controllers\Controller;
use App\Models\Sercurity\MemberProject;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Validator;

class MemberProjectController extends Controller
{
    /**
     * Display a listing of the resource.
     *
     * @return \Illuminate\Http\Response
     */
    public function index(Request $request)
    {
        $data = MemberProject::with('project')->with('member')->paginate(6);
        $page = 1;
        if (isset($request->page)) {
            $page = $request->page;
        }
        $index = ($page - 1) * 6 + 1;
        if ($data) {
            return response()->json([
                'status' => 200,
                'message' => 'Data projects list.',
                'data' => $data,
                'index' => $index
            ]);
        } else {
            return response()->json([
                'status' => 404,
                'message' => 'Data project not found.'
            ]);
        }
    }

    /**
     * Store a newly created resource in storage.
     *
     * @param  \Illuminate\Http\Request  $request
     * @return \Illuminate\Http\Response
     */
    public function store(Request $request)
    {
        $input = $request->all();
        $validator = Validator::make($input, [
            'project_id' => 'required',
            'member_id' => 'required',
            'role' => 'required',
            'time_member_join' => 'required',
            'time_member_completed' => 'required',
        ]);
        if($validator->fails()){ 
            return response()->json([
                'status' => 400,
                'message' => 'Please fill out the information completely.',
                'error' => $validator->errors()
            ]);
        } else {
            if (is_null($request->project_id) || is_null($request->member_id)) {
                return response()->json([
                    'status' => 404,
                    'message' => 'Check registered project or member, please!',
                ]);
            } else {
                $data = MemberProject::create($input);
                if ($data) {
                    return response()->json([
                        'status' => 200,
                        'message' => 'Member project created successfully.',
                        'data' => $data
                    ]);
                } else {
                    return response()->json([
                        'status' => 400,
                        'message' => 'Member project created fail.',
                    ]);
                }
            }
        }
    }

    /**
     * Show the specified resource.
     *
     * @param  int  $id
     * @return \Illuminate\Http\Response
     */
    public function edit($project_id, $member_id)
    {
        $data = MemberProject::with('project')->with('member')
        ->where('member_project.project_id', $project_id)
        ->where('member_project.member_id', $member_id)
        ->get();
        if ($data->isEmpty()) {
            return response()->json([
                'status' => 404,
                'message' => 'Data not found.',
            ]);
        }
        return response()->json([
            'status' => 200,
            'message' => 'Data content detail.',
            'data' => $data
        ]);
    }

    /**
     * Update the specified resource in storage.
     *
     * @param  \Illuminate\Http\Request  $request
     * @param  int  $id
     * @return \Illuminate\Http\Response
     */
    public function save(Request $request, $id1, $id2)
    {
        $data = MemberProject::where('project_id', $id1)
        ->where('member_id', $id2)
        ->get();
        if ($data) {
            $input = $request->all();
            $validator = Validator::make($input, [
                'project_id' => 'required',
                'member_id' => 'required',
                'role' => 'required',
                'time_member_join' => 'required',
                'time_member_completed' => 'required',
            ]);
            if($validator->fails()){ 
                return response()->json([
                    'status' => 400,
                    'message' => 'Please fill out the information completely.',
                    'error' => $validator->errors()
                ]);
            } else {
                $result = MemberProject::where('project_id', $id1)
                ->where('member_id', $id2)
                ->update([
                    'project_id' => $request->project_id,
                    'member_id' => $request->member_id,
                    'role' => $request->role,
                    'time_member_join' => $request->time_member_join,
                    'time_member_completed' => $request->time_member_completed
                ]);
                if ($result) {
                    return response()->json([
                        'status' => 200,
                        'message' => 'Member project updated successfully.'
                    ]);
                } else {
                    return response()->json([
                        'status' => 400,
                        'message' => 'Member project update fail.'
                    ]);
                }
            }
        } else {
            return response()->json([
                'status' => 404,
                'message' => 'Member project not found.'
            ]);
        }
    }

    /**
     * Search the specified resource from storage.
     *
     * @param  int  $name
     * @return \Illuminate\Http\Response
     */
    public function search($name)
    {
        $data = MemberProject::query()->with([
            'project' => function ($query) use ($name) {
                $query->where('project.name','like','%'.$name.'%');
            }
        ])->with('member')->get();

        // Filter value == null
        function filterResult($data)
        {
            for ($i = 0; $i < sizeof($data); $i++) {
                if (is_null($data[$i]->project)) unset($data[$i]);
            }
            return $data;
        }
        filterResult($data);

        if ($data->isNotEmpty()) {
            return response()->json([
                'status' => 200,
                'message' => 'Data content detail.',
                'data' => $data,
            ]);
        } else {
            return response()->json([
                'status' => 404,
                'message' => 'Data not found.'
            ]);
        }
    }

    /**
     * Select the specified resource from storage.
     *
     * @param  int  $name
     * @return \Illuminate\Http\Response
     */
    public function showForeignKey($name)
    {
        $data = DB::table($name)
        ->select($name . '.name', $name . '.id')
        ->get();
        if ($data->isNotEmpty()) {
            return response()->json([
                'status' => 200,
                'message' => $name . ' content detail.',
                'data' => $data
            ]);
        } else {
            return response()->json([
                'status' => 404,
                'message' => $name . ' not found.',
            ]);
        }
    }
}
