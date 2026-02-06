<?php

namespace App\Http\Services\Common;

use App\Http\Resources\Common\UserResource;
use App\Http\Resources\Pagination\BasePaginationResource;
use App\Models\User;
use Firebase\JWT\JWT;
use Firebase\JWT\Key;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Validator;
use Illuminate\Support\Str;

class AuthService
{
    public function registration($request)
    {
        $role = USER;
        if ($request->role == 'user' || $request->role == 'trainer') {
            if ($request->role == 'user') {
                $role = USER;
            } else {
                $role = TRAINER;
            }
        } else {
            return errorResponse(__('Invalid role'));
        }
        $chekcEmail = User::where(['email' => $request->email])->exists();
        if ($chekcEmail) {
            return errorResponse(__('Email already exists'));
        }
        $checkPhone = User::where(['phone' => $request->phone])->exists();
        if ($checkPhone) {
            return errorResponse(__('Phone already exists'));
        }
        $checkOtp = otpVerify($request->email, $request->otp, 'registration');
        if ($checkOtp['status'] == false) {
            return errorResponse($checkOtp['message']);
        }
        $data = [
            'uuid' => Str::uuid(),
            'name' => $request->name,
            'email' => $request->email,
            'password' => Hash::make($request->password),
            'phone' => $request->phone,
            'role' => $role,
            'status' => ACTIVE,
            'is_mail_verified' => ENABLE,
        ];

        try {
            $user = User::create($data);
            $token = $user->createToken($user->uuid.'user')->accessToken;

            return successResponse(__('Registration successfull'), ['token' => $token, 'role' => $request->role]);
        } catch (\Exception $e) {
            return errorResponse($e->getMessage());
        }
    }

    // Login
    public function login($request)
    {
        $user = User::where(['email' => $request->email])->first();
        if (empty($user)) {
            return errorResponse(__('User not found'));
        }
        if (! Hash::check($request->password, $user->password)) {
            return errorResponse(__('Password not matched'));
        }
        $token = $user->createToken($user->uuid.'user')->accessToken;

        return successResponse(__('Login successfull'), ['token' => $token, 'role' => $user->role == USER ? 'user' : ($user->role == TRAINER ? 'trainer' : 'admin')]);
    }

    // Reset Password
    public function resetPassword($request)
    {

        $decoded = JWT::decode($request->token, new Key(env('JWT_SECRET'), 'HS256'));
        $decoded_array = (array) $decoded;
        $decoded_array['email'];

        $user = User::where(['email' => $decoded_array['email']])->first();
        if (empty($user)) {
            return errorResponse(__('User not found'));
        }
        $user->password = Hash::make($request->password);
        $user->save();

        return successResponse(__('Password reset successfully'));
    }

    // Update Password
    public function updatePassword($request)
    {
        $user = Auth::guard('checkUser')->user();
        if ($request->new_password) {
            $validator = Validator::make($request->all(), [
                'old_password' => 'required',
                'new_password' => 'required|min:6',
            ]);
            if ($validator->fails()) {
                return errorResponse($validator->errors()->first());
            }
        }
        $user = User::where(['email' => $user->email])->first();
        if (! Hash::check($request->old_password, $user->password)) {
            return errorResponse(__('Old password not matched'));
        }
        $user->password = Hash::make($request->new_password);
        $user->save();

        return successResponse(__('Password updated successfully'));
    }

    public function createTrainer($request)
    {
        // Validate the request data
        $validator = Validator::make($request->all(), [
            'name' => 'required',
            'email' => 'required|email|unique:users',
            'password' => 'required',
            'phone' => 'required|unique:users',
        ]);

        if ($validator->fails()) {
            return errorResponse($validator->errors()->first());
        }

        // Create the trainer account
        $data = [
            'uuid' => Str::uuid(),
            'name' => $request->name,
            'email' => $request->email,
            'password' => Hash::make($request->password),
            'phone' => $request->phone,
            'role' => TRAINER,
            'status' => ACTIVE,
            'is_mail_verified' => ENABLE,
        ];

        try {
            $trainer = User::create($data);
            $token = $trainer->createToken($trainer->uuid.'trainer')->accessToken;

            return successResponse(__('Trainer account created successfully'), ['token' => $token]);
        } catch (\Exception $e) {
            return errorResponse($e->getMessage());
        }
    }

    //trainer List for admin
    public function trainerList($request)
    {
        // Initialize the query for trainers
        $query = User::where('role', TRAINER);

        // Add search functionality if provided
        if ($request->has('search') && ! empty($request->search)) {
            $query->where('name', 'like', '%'.$request->search.'%');
        }

        $per_page = $request->per_page ?? PERPAGE_PAGINATION;

        $trainers = $query->orderBy('created_at', 'desc')->paginate($per_page);

        // Prepare the data
        $data = $trainers->map(function ($trainer) {
            return [
                '_id' => $trainer->id,
                'name' => $trainer->name,
                'phone' => $trainer->phone,
                'email' => $trainer->email,
                'role' => $trainer->role == TRAINER ? 'trainer' : 'admin',
                'createdAt' => $trainer->created_at,
                'updatedAt' => $trainer->updated_at,
                'skills' => $trainer->skills ?? [],
                'gender' => $trainer->gender ?? null,
                'image' => $trainer->image ?? null,
            ];
        });

        // Return the paginated response
        return successResponse(__('Trainer list'), [
            'current_page' => $trainers->currentPage(),
            'total_pages' => $trainers->lastPage(),
            'total_items' => $trainers->total(),
            'per_page' => $trainers->perPage(),
            'docs' => $data,
        ]);
    }

    //user list
    public function userList($request)
    {
        $sort_by = $request->sort_by ?? 'id';
        $dir = $request->dir ?? 'desc';
        $per_page = $request->limit ?? PERPAGE_PAGINATION;
        $lang = $request->langCode ?? 'en';

        $query = User::query();

        // Exclude records where role is 'admin'
        $query->where('role', '!=', ADMIN);

        // Filter by subscription status if specified
        if ($request->filled('subscription')) {
            if ($request->subscription === 'active') {
                $query->whereHas('userSubscription', function ($q) {
                    $q->where('active', 1)->where('is_paid', 1);
                });
            } elseif ($request->subscription === 'inactive') {
                $query->whereDoesntHave('userSubscription', function ($q) {
                    $q->where('active', 1)->where('is_paid', 1);
                });
            }
        }

        // Define fields to search
        $searchableFields = ['name', 'email', 'phone', 'role'];

        // Apply search filter
        if ($request->filled('search')) {
            $searchTerm = '%'.$request->search.'%';

            $query->where(function ($q) use ($searchTerm, $searchableFields) {
                foreach ($searchableFields as $field) {
                    $q->orWhere($field, 'like', $searchTerm);
                }
            });
        }

        $data = $query->orderBy($sort_by, $dir)->paginate($per_page);

        return successResponse(__('Successfully retrieved user list'), new BasePaginationResource(UserResource::collection($data)));
    }

    //for group trainer
    public function groupTrainerList($request)
    {
        // Initialize the query for trainers
        $query = User::where('role', TRAINER);

        // Add search functionality if provided
        if ($request->has('search') && ! empty($request->search)) {
            $query->where('name', 'like', '%'.$request->search.'%');
        }

        $per_page = $request->per_page ?? PERPAGE_PAGINATION;

        $trainers = $query->orderBy('created_at', 'desc')->paginate($per_page);

        // Prepare the data
        $data = $trainers->map(function ($trainer) {
            return [
                '_id' => $trainer->id,
                'name' => $trainer->name,
                'phone' => $trainer->phone,
                'email' => $trainer->email,
                'role' => $trainer->role == TRAINER ? 'trainer' : 'admin',
                'createdAt' => $trainer->created_at,
                'updatedAt' => $trainer->updated_at,
                'skills' => $trainer->skills ?? [],
                'gender' => $trainer->gender ?? null,
                'image' => $trainer->image ?? null,
            ];
        });

        return successResponse(__('Trainer list'), $data);
    }

    //user details
    public function show($request)
    {
        $user = User::find($request->_id);
        if (! $user) {
            return errorResponse(__('User not found.'));
        }

        return successResponse(__('User details fetched successfully'), UserResource::make($user));
    }

    //admin deleting user
    public function delete($request)
    {
        $user = User::find($request->_id);
        if (! $user) {
            return errorResponse(__('User not found.'));
        }
        $user->delete();

        return successResponse(__('User deleted successfully'));
    }
}
