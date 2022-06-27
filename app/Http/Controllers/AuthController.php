<?php

namespace App\Http\Controllers;

use App\Models\Clinic;
use App\Models\ClinicField;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Hash;
use App\Models\Doctor;
use App\Models\WorkSchedule;
use App\Services\Helper;

use Illuminate\Support\Facades\Route;
use Illuminate\Support\Facades\Log;


class AuthController extends Controller
{

	public function __construct()
	{
		$this->middleware('auth:api', ['except' => ['login', 'refresh', 'me']]);
	}

	public function login(Request $request)
	{
		try {

			$validatedData = $request->validate([
				'email' => 'required|string|email',
				'password' => 'required|string',
			], [
				'email.required' => 'Adresa de mail este obligatorie.',
				'email.string' => 'Adresa de mail trebuie să fie în format text.',
				'email.email' => 'Adresa de mail trebuie să fie validă.'
			]);

			$token = Auth::attempt($validatedData);

			if (!$token) {
				return response()->error('Email sau parolă greșită.');
			}

			$user = Auth::user();

			$clinic_name = Clinic::find($user->id_clinic)->name;
			$field_name = ClinicField::find($user->id_field)->name;
			$doctor_type_name = $user->doctor_type == 1 ? 'Doctor primar' : 'Doctor specialist';

			$user->clinic_name = $clinic_name;
			$user->field_name = $field_name;
			$user->doctor_type_name = $doctor_type_name;

			$data = [
				'user' => $user,
				'token' => $token,
			];

			return response()->success($data);
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

	public function changePassword(Request $request)
	{
		try {

			if (!isset($request->password) || empty($request->password) || !isset($request->password_confirmation) || empty($request->password_confirmation)) {
				$msg = Helper::$generalError;
				return response()->error($msg);
			}

			$input = array(
				'password' => trim($request->input('password')),
				'password_confirmation' => trim($request->input('password_confirmation'))
			);

			if ($input['password'] !== $input['password_confirmation']) {
				$msg = Helper::$generalError;
				return response()->error($msg);
			}

			$authUser = Auth::user();

			$user = Doctor::find($authUser->id);
			if (empty($user)) {
				$msg = Helper::$generalError;
				return response()->error($msg);
			}

			$user->password = Hash::make($input['password']);
			$user->save();

			return response()->success('Parolă schimbată cu succes!');
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

	public function updateProfile(Request $request)
	{
		try {

			$authUser = Auth::user();
			$cleanEmail = trim(stripslashes($request->email));

			if ($authUser->email !== $cleanEmail) {

				$firstValidation = $request->validate([
					'email' => 'required|string|email|max:255|unique:users'
				], [
					'email.required' => 'Adresa de mail este obligatorie.',
					'email.string' => 'Adresa de mail trebuie să fie în format text.',
					'email.email' => 'Adresa de mail trebuie să fie validă.',
					'email.max' => 'Adresa de mail este prea lungă.',
					'email.unique' => 'Există deja un cont cu această adresă de mail.'
				]);
			}

			$validatedData = $request->validate([
				'first_name' => 'required|string|max:255',
				'last_name' => 'required|string|max:255',
				'ci_serie' => 'required|string|min:2|max:2',
				'ci_numar' => 'required|string|min:6|max:6',
				'phone' => 'required',
			], [
				'first_name.required' => 'Prenumele este obligatoriu.',
				'first_name.string' => 'Prenumele trebuie să fie în format text.',
				'first_name.max' => 'Prenumele este prea lung.',

				'last_name.required' => 'Numele este obligatoriu.',
				'last_name.string' => 'Numele trebuie să fie în format text.',
				'last_name.max' => 'Numele este prea lung.',

				'ci_serie.required' => 'Seria CI este obligatorie.',
				'ci_serie.string' => 'Seria CI trebuie să fie în format text.',
				'ci_serie.min' => 'Minim 2 caractere.',
				'ci_serie.max' => 'Maxim 2 caractere',

				'ci_numar.required' => 'Numărul CI este obligatoriu.',
				'ci_numar.string' => 'Numărul CI trebuie să fie în format text.',
				'ci_numar.min' => 'Minim 6 caractere.',
				'ci_numar.max' => 'Maxim 6 caractere'
			]);

			// If authenticated user is not found
			$user = Doctor::find($authUser->id);
			if (empty($user)) {
				$msg = Helper::$generalError;
				return response()->error($msg);
			}

			// Update user
			$user->first_name = Helper::cleanData($validatedData['first_name']);
			$user->last_name = Helper::cleanData($validatedData['last_name']);
			$user->email = isset($firstValidation['email']) ? $firstValidation['email'] : $user->email;
			$user->phone = Helper::cleanData($validatedData['phone']['nationalNumber']);
			$user->international_number = Helper::cleanData($validatedData['phone']['internationalNumber']);
			$user->dial_code = Helper::cleanData($validatedData['phone']['dialCode']);
			$user->iso_code = Helper::cleanData($validatedData['phone']['isoCode']);
			$user->ci_serie = strtoupper(Helper::cleanData($validatedData['ci_serie']));
			$user->ci_numar = Helper::cleanData($validatedData['ci_numar']);

			$user->save();

			return response()->success('Informații personale actualizate cu succes!');
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

	public function updateSchedule(Request $request)
	{
		try {

			$authUser = Auth::user();

			$input = $request->all();

			foreach ($input as $key => &$value) {

				// get everything after day_
				$dayAndFromOrTo = explode('day_', $key)[1];
				// split string in 2 substrings, one before and one after _
				$dayOrFromOrTo = explode('_', $dayAndFromOrTo);
				// get day as the substring before _
				$day = (int)$dayOrFromOrTo[0];
				// get from / to as the substring after _
				$fromOrTo = $dayOrFromOrTo[1];

				// if seconds are not found
				if (strlen($value) == 5) {
					$value .= ':00';
				}

				$schedule = WorkSchedule::where('id_doctor', '=', $authUser->id)->where('day', '=', $day)->first();

				$schedule->$fromOrTo = !empty($value) ? trim(stripslashes(strip_tags($value))) : null;
				$schedule->save();
			}
			return response()->success('Program de lucru actualizat cu succes!');
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

	public function logout()
	{
		try {

			Auth::logout();

			return response()->success('V-ați deconectat cu succes!');
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

	public function me()
	{
		try {

			$user = Auth::user();

			$clinic_name = Clinic::find($user->id_clinic)->name;
			$field_name = ClinicField::find($user->id_field)->name;
			$doctor_type_name = $user->doctor_type == 1 ? 'Doctor primar' : 'Doctor specialist';

			$user->clinic_name = $clinic_name;
			$user->field_name = $field_name;
			$user->doctor_type_name = $doctor_type_name;

			$schedules = WorkSchedule::where('id_doctor', '=', $user->id)->get();

			$res = [
				'user' => $user,
				'schedules' => $schedules
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

	public function refresh()
	{
		try {

			$user = Auth::user();
			$token = Auth::refresh();

			return response()->success([
				'user' => $user,
				'token' => $token
			]);
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
