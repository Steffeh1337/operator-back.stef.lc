<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

use App\Services\Helper;

use Illuminate\Support\Facades\Route;
use Illuminate\Support\Facades\Log;

use App\Models\NotificationDoctor as Notification;

class NotificationsController extends Controller
{
	private $authUser = null;

	public function __construct()
	{
		$this->authUser = Auth::user();
	}

	public function index(Request $request)
	{
		try {

			$type = isset($request->type) ? (int)$request->type : -1;

			$notificationsInit = Notification::select('id', 'read', 'thumbnail', 'slug', 'content', 'created_at')->where('id_doctor', '=', $this->authUser->id);

			if ($type > -1) {
				$notificationsInit->where('read', '=', $type);
			}

			$notifications = $notificationsInit->orderBy('created_at', 'desc')->get();

			return response()->success($notifications);
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

	public function find(Notification $notification)
	{
		try {

			if (!isset($notification) || empty($notification)) {
				return response()->error(Helper::$generalError);
			}
			return response()->success($notification);
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

	public function count()
	{
		try {
			$number = Notification::where([
				['id_doctor', '=', $this->authUser->id],
				['read', '=', 0]
			])->count();

			return response()->success($number);
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

	public function markAsRead(Notification $notification)
	{
		try {

			$notification->read = $notification->read == 1 ? 0 : 1;
			$notification->save();

			$status = $notification->read == 1 ? 'citită' : 'necitită';

			return response()->success("Notificarea a fost marcată ca {$status}!");
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

	public function registerDevice()
	{
		return response()->error('Această funcție este disponibilă doar pe un device real!');
	}
}
