<?php

namespace App\Http\Controllers;

use App\User;
use App\Task;
use App\Task_User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Validator;
use Illuminate\Support\Facades\Auth;

use function GuzzleHttp\Promise\all;

class TaskController extends Controller
{
    private $success_status = 200;

    // ------------- [ Create New Task ] ----------------
    public function createTask(Request $request)
    {
        $user = Auth::user();

        $validator = Validator::make($request->all(),
            [
                'task.title'         =>  'required'
            ]
        );

        if($validator->fails()) {
            return response()->json(["validationErrors" => $validator->errors()]);
        }

        $taskInput = array(
            'title'         =>      $request->task["title"],
            'isShared'      =>      false,
            'isCompleted'   =>      false
        );
        $task = Task::create($taskInput);

        // $taskUserInput = array(
        //     'task_id'       =>      $task->id,
        //     'user_id'       =>      $user->id
        // );
        // $taskUser = Task_User::create($taskInput);
        $user->tasks()->attach($task->id); //record is created in DB.

        if(!is_null($task)) {
            $success['status']  =   "success";
            $success['data']    =   $task;
        }

        return response()->json(['success' => $success], $this->success_status);
    }


    // --------------- [ Task Listing Based on User Auth Token ] ------------
    public function taskListing()
    {
        $user = Auth::user();
        // $tasksIds   = Task_User::where('user_id', $user->id)->pluck('task_id')->toArray();
        // $tasks      = Task::whereIn('id', $tasksIds)->get();
        $tasks = $user->tasks()->get();

        // $success['status']  =   "success";
        // $success['data']    =   $tasks;

        return response()->json(['tasks' => $tasks]);
    }


// ---------------------- [ Task Detail ] -----------------------

    public function taskDetail($task_id)
    {
        $user = Auth::user();
        $task = $user->tasks()->wherePivot('task_id' ,$task_id)->first();

        if(!is_null($task)) {
            $taskUserIds = $task->users()->pluck('user_id')->toArray();
            // $taskUserIds = Task::where('id', $task_id)->first()->users->pluck('id')->toArray();
            $users = User::where('id', '<>', $user->id)->select('id','name')->get()->toArray();;
            foreach ($users as $key => $user) {
                $users[$key]['isShared']= in_array($user['id'], $taskUserIds);
            }
            $success['status']  =   "success";
            $success['data']    =   $task;
            $success['users']   =   $users;
            return response()->json(['success' => $success]);
        }

        else {
            $success['status']  =   "failed";
            $success['message'] =   "Whoops! no detail found";

            return response()->json(['success' => $success]);
        }
    }


// ------------------ [ Share Task ] -------------------
public function shareTask(Request $request)
{
    $user = Auth::user();

    $validator = Validator::make($request->all(),
        [
            'task_id'       =>      'required',
            'user_id'        =>     'required'
        ]
    );

    // if validation fails
    if($validator->fails()) {
        return response()->json(["validationErrors" => $validator->errors()]);
    }
    $isSaved = 0;

    $task = $user->tasks()->wherePivot('task_id' ,$request->task_id)->get();

    if(!is_null($task)) {
        $taskUserInput = array(
            'task_id'       =>      $request->task_id,
            'user_id'       =>      $request->user_id
        );
        $taskUser = Task_User::create($taskUserInput);

        if(!is_null($taskUser)) {
            $inputData = array(
                'isShared'  =>      true
            );
            $isSaved = $user->tasks()->wherePivot('task_id' ,$request->task_id)->update($inputData);
        }
    }

    if($isSaved == 1 ) {
        $success['status']  =   "success";
        $success['message'] =   "Task has been shared successfully";
    } else {
        $success['status']  =   "failed";
        $success['message'] =   "Failed to shared the task please try again";
    }

    return response()->json(['success' => $success], $this->success_status);

}


// ------------------ [ Update Task ] ------------------

    public function updateTask(Request $request, $task_id)
    {
        $user = Auth::user();

        $validator = Validator::make($request->all(),
            [
                'task.title'         =>      'required',
                'task.isCompleted'   =>      'required'
            ]
        );

        // if validation fails
        if($validator->fails()) {
            return response()->json(["validationErrors" => $validator->errors()]);
        }

        $inputData = array(
            'title'             =>      $request->task["title"],
            'isCompleted'       =>      boolval($request->task["isCompleted"])
        );

        // $task = Task::where('id', $request->task_id)->where('user_id', $user->id)->update($inputData);
        $task = $user->tasks()->wherePivot('task_id' ,$request->task_id)->update($inputData);

        if($task == 1) {
            $success['status']  =       "success";
            $success['message'] =       "Task has been updated successfully";
        }

        else {
            $success['status']  =       "failed";
            $success['message'] =       "Failed to update the task please try again";
        }

        return response()->json(['success' => $success], $this->success_status);

    }


    // ---------------------- [ Delete Task ] --------------------------

    public function deleteTask($id) {

        $user = Auth::user();
        $task = Task::findOrFail($id);

        if(!is_null($task)) {
            $response   =   Task::where('id', $id)->delete();
            if($response == 1) {
                $success['status']  =   'success';
                $success['message'] =   'Task has been deleted successfully';
                return response()->json(['success' => $success], $this->success_status);
            }
        }
    }
}
