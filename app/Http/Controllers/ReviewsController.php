<?php

namespace App\Http\Controllers;

use App\Services\Helper;

use Illuminate\Support\Facades\Route;
use Illuminate\Support\Facades\Log;

use App\Models\Review;
use App\Models\User;

use Illuminate\Support\Facades\Auth;

class ReviewsController extends Controller
{
	private $authUser = null;

	public function __construct()
	{
		$this->authUser = Auth::user();
	}

	public function index()
	{
		try {

			$reviewsNo = Review::where('id_doctor', '=', $this->authUser->id)->whereNotNull('done_user')->whereNotNull('rating_user')->count();
			$rating = Review::where('id_doctor', '=', $this->authUser->id)->whereNotNull('rating_user')->avg('rating_user');

			$data = [];
			$data['rating'] = $rating;
			$data['flooredRating'] = !empty($rating) ? floor($rating) : 0;
			$data['reviewsNo'] = $reviewsNo;

			$reviews = Review::select('id', 'id_user', 'rating_user', 'comment_user', 'done_user')->where('id_doctor', '=', $this->authUser->id)->whereNotNull('rating_user')->orderBy('id', 'desc')->get();

			foreach ($reviews as &$rev) {

				$submitter = User::find($rev->id_user);

				if (empty($submitter)) {
					$msg = Helper::$generalError;
					return response()->error($msg);
				}

				$rev->user_name = $submitter->name;
			}

			$res = [
				'reviews' => $reviews,
				'data' => $data
			];

			return response()->success($res);
		} catch (\Exception $ex) {
			$msg = Helper::$generalError;

			if (Helper::getDevelopmentMode()) {
				$msg = $ex->getMessage() . ' at line ' . $ex->getLine();
			}

			$data = [
				'app' => Helper::getAppName(),
				'location' => Route::currentRouteName(),
				'action' => Route::currentRouteAction(),
				'message' => "Exception {$ex->getMessage()} at line {$ex->getLine()}"
			];

			Log::error($data);
			return response()->error($msg);
		} catch (\Throwable $ex) {
			$msg = Helper::$generalError;

			if (Helper::getDevelopmentMode()) {
				$msg = $ex->getMessage() . ' at line ' . $ex->getLine();
			}

			$data = [
				'app' => Helper::getAppName(),
				'location' => Route::currentRouteName(),
				'action' => Route::currentRouteAction(),
				'message' => "Throw {$ex->getMessage()} at line {$ex->getLine()}"
			];

			Log::error($data);
			return response()->error($msg);
		}
	}
}
