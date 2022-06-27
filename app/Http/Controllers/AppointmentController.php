<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Route;
use Illuminate\Support\Facades\Log;

use App\Models\Appointment;
use App\Models\User;
use App\Models\Notification;
use App\Models\NotificationDoctor;
use App\Models\Doctor;

use App\Services\Helper;

use Illuminate\Support\Facades\DB;


class AppointmentController extends Controller
{

	private $authUser = null;

	public function __construct()
	{
		try {
			$this->authUser = Auth::user();
		} catch (\Exception $ex) {
			return response()->error('Ne pare rău, dar trebuie să fiți autentificat pentru a efectua această acțiune.');
		} catch (\Throwable $ex) {
			return response()->error('Ne pare rău, dar trebuie să fiți autentificat pentru a efectua această acțiune.');
		}
	}

	public function getFiltersData()
	{
		try {
			$userIds = Appointment::where('id_doctor', '=', $this->authUser->id)->distinct('id_user')->pluck('id_user');

			$users = [];
			foreach ($userIds as &$u) {
				$users[] = User::select('id', 'last_name', 'first_name')->where('id', '=', $u)->first();
			}

			return response()->success($users);
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

	public function index(Request $request)
	{
		try {

			$currentTime = date('Y-m-d H:i:s');

			$draw = $request['draw'];
			$columns = $request['columns'];
			$length = (int)$request['length'];
			$start = (int)$request['start'];
			$skip = $length * $start;

			// advanced search
			$user_name = trim($request['search']['user_name']);
			$appointments_type = (int)$request['search']['appointments_type'];

			$sql = "SELECT
						a.id as id_appointment,
						a.id_user,
						a.start_date,
						a.end_date,
						a.active,
						a.done,
						a.amount,
						a.cancelled_user,
						a.cancelled_user_date,
						a.cancelled_user_obs,
						a.cancelled_doctor,
						a.cancelled_doctor_date,
						a.cancelled_doctor_obs,
						a.created_at,
						a.updated_at,
						u.id,
						u.name
					FROM appointments as a 
					LEFT JOIN users as u on u.id = a.id_user
					WHERE id_doctor = {$this->authUser->id}
					";

			$allRecords = DB::select(DB::raw($sql));
			$recordsTotal = count($allRecords);

			if (isset($request['search']) && !empty($request['search']) && isset($request['search']['user_name']) && !empty($request['search']['user_name'])) {
				$sSearch = $request['search']['user_name'];
				$sql .= " AND name LIKE '%{$sSearch}%'";
			}

			if ($appointments_type > 0) {
				switch ($appointments_type) {
					case 1:
						// Trecute
						$sql .= " AND a.end_date < '{$currentTime}'";
						break;

					case 2:
						// Viitoare
						$sql .= " AND a.start_date > '{$currentTime}'";
						break;

					default:
						// nothing
						break;
				}
			}

			if (isset($request['order']) && !empty($request['order']) && count($request['order']) == 1) {
				$order = $request['order'][0];
				$sql .= " ORDER BY {$columns[$order['column']]['data']} {$order['dir']} ";
			}

			$recordsFiltered = DB::select(DB::raw($sql));

			if ($length != -1) {
				$sql .= " LIMIT {$length} OFFSET {$start}";
			}

			$records = DB::select(DB::raw($sql));

			foreach ($records as &$record) {

				$record->actions = '';

				if ($record->start_date > $currentTime && $record->cancelled_doctor == 0 && $record->cancelled_user == 0) {
					$record->actions .= "<button type='button' value=" . ($record->id_appointment) . " class='cancel btn btn-sm btn-danger'>Anulați</button>";
				}

				$record->amount .= ' RON';
			}

			$datatable = array(
				'draw' => $draw,
				'recordsTotal' => $recordsTotal,
				'recordsFiltered' => count($recordsFiltered),
				'data' => $records
			);

			return response()->success($datatable);
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

	public function cancel(Appointment $appointment, Request $request)
	{
		try {
			$currentDate = date('Y-m-d H:i:s');

			if (empty($appointment)) {
				$msg = Helper::$generalError;
				return response()->error($msg);
			}

			$reason = isset($request->motiv) && !empty($request->motiv) ? Helper::cleanData($request->motiv) : null;

			if (empty($reason)) {
				return response()->error('Ne pare rău, dar trebuie să specificați motivul anulării programării!');
			}

			$appointment->cancelled_doctor = 1;
			$appointment->cancelled_doctor_date = $currentDate;
			$appointment->cancelled_doctor_obs = $reason;
			$appointment->active = 0;

			$appointment->save();

			// Create cancel notification for doctor
			$drNotification = new NotificationDoctor();
			$drNotification->id_doctor = $this->authUser->id;
			$drNotification->slug = "Ați anulat o programare!";
			$drNotification->content = "Stimate {$this->authUser->first_name}, prin această notificare vă înștiințăm că ați anulat cu succes programarea cu numărul {$appointment->id}, din motivul: {$appointment->cancelled_doctor_obs}.";
			$drNotification->save();

			unset($drNotification);

			// get user name for notification
			$user_name = User::find($appointment->id_user)->name;

			// Create cancel notification for user
			$notification = new Notification();
			$notification->id_user = $appointment->id_user;
			$notification->slug = "Programare anulată de către doctor!";
			$notification->content = "Stimate {$user_name}, prin această notificare vă înștiințăm că doctorul {$this->authUser->last_name} {$this->authUser->first_name} a anulat programarea cu numărul {$appointment->id}, din motivul: {$appointment->cancelled_doctor_obs}.";
			$notification->save();

			unset($notification);

			return response()->success('Programarea a fost anulată cu succes!');
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
