<?php

namespace App\Http\Controllers;

use App\changeover_time;
use App\Exercise;
use App\WorkoutPlan;
use App\Category;
use App\Goal;
use Http\Response;
use Illuminate\Support\Arr;
use Illuminate\Http\Request;
use App\Http\Controllers\Goals;

class WorkoutPlans extends Controller
{
  public function create(request $request)
  {
    $goalDetails = Goal::where('goal', '=', $request['goal'])->first()->only('goal','rep_time', 'rest_time', 'reps', 'changeover_time', 'sets');
    $categories = $request['categories'];
    // iterate over categories and return an instance of each category model
    $exercises = collect($categories)->map(function($cat) {
      $categoryModel = Category::where('category', '=', $cat)->first();
      return $categoryModel->exercises()->pluck('title','description');
    });

    $exercises = collect(Arr::flatten($exercises))->shuffle()->unique()->values();
    $desiredTime = $request['time'];
    $numOfEx = $this->num_of_ex($goalDetails, $desiredTime);
    $returnedEx = $exercises->take($numOfEx);

    return response()->Json(array(
     'workoutFocus' => $categories,
     'workoutGoal' => $goalDetails['goal'],
     'workoutTime' => $desiredTime,
     'restTime' => $goalDetails['rest_time'],
     'sets' => $goalDetails['sets'],
     'targetReps' => $goalDetails['reps'],
     'Workout'=> $returnedEx,
   ));
  }

  public function num_of_ex($goalDetails, $desiredTime)
  {
   $goal = $goalDetails['goal'];
   $rep_time = $goalDetails['rep_time'];
   $rest_time = $goalDetails['rest_time'];
   $reps = $goalDetails['reps'];
   $changeover_time = $goalDetails['changeover_time'];
   $sets = $goalDetails['sets'];
   $desiredTimeSecs = ($desiredTime * 60) + $changeover_time;

   $oneExercise = (($reps * $rep_time) * $sets) + ($rest_time * ($sets - 1)) + $changeover_time;
   $numOfEx = floor($desiredTimeSecs / $oneExercise);

   return $numOfEx;
 }
}







