<?php

namespace App\Http\Requests\Auth;

use Illuminate\Auth\Events\Lockout;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\RateLimiter;
use Illuminate\Support\Str;
use Illuminate\Validation\ValidationException;
use Symfony\Component\ErrorHandler\Debug;

class LoginRequest extends FormRequest
{
    /**
     * Determine if the user is authorized to make this request.
     */
    public function authorize(): bool
    {
        return true;
    }

    /**
     * Get the validation rules that apply to the request.
     *
     * @return array<string, \Illuminate\Contracts\Validation\ValidationRule|array<mixed>|string>
     */
  /*  public function rules(): array
    {
        return [
            'id' => ['required'],
            'password' => ['required', 'string'],
        ];
    }*/

    public function rules(): array
    {
        Log::debug($this);
        if ($this->has('email')) {
            return [

                'email' => ['required', 'email', 'exists:users,email', 'min:1'],
                'password' => ['required', 'string'],
            ];
        }  else {
            return [
                'id' => ['required', 'exists:users,id', 'min:1'],
                'password' => ['required', 'string'],
            ];
        }

    }

    /**
     * Attempt to authenticate the request's credentials.
     *
     * @throws \Illuminate\Validation\ValidationException
     */


    public function authenticate(): void
    {
        $this->ensureIsNotRateLimited();

    /*    if (! Auth::attempt($this->only('id', 'password'), $this->boolean('remember'))) {
            RateLimiter::hit($this->throttleKey());
            throw ValidationException::withMessages([
                'credentials' => __('auth.failed'),
            ]);
        }*/

        $credentials = [];

        if ($this->has('email')) {
            $credentials = $this->only('email', 'password');
        } elseif ($this->has('id')) {
            $credentials = $this->only('id', 'password');
        } else {
            throw ValidationException::withMessages([
                'credentials' => 'Either email or ID is required for login.',
            ]);
        }

        if (! Auth::attempt($credentials, $this->boolean('remember'))) {
            RateLimiter::hit($this->throttleKey());
            throw ValidationException::withMessages([
                'credentials' => __('auth.failed'),
            ]);
        }


        RateLimiter::clear($this->throttleKey());
    }

    /**
     * Ensure the login request is not rate limited.
     *
     * @throws \Illuminate\Validation\ValidationException
     */
    public function ensureIsNotRateLimited(): void
    {
        if (! RateLimiter::tooManyAttempts($this->throttleKey(), 3)) {
            return;
        }

        event(new Lockout($this));

        $seconds = RateLimiter::availableIn($this->throttleKey());

        throw ValidationException::withMessages([
            'credentials' => trans('auth.throttle', [
                'seconds' => $seconds,
                'minutes' => ceil($seconds / 60),
            ]),
        ]);
    }

    /**
     * Get the rate limiting throttle key for the request.
     */
    public function throttleKey(): string
    {if ($this->has('email')) {
        return Str::transliterate(Str::lower($this->input('email')).'|'.$this->ip());
    } else{
        return Str::transliterate(Str::lower($this->input('id')).'|'.$this->ip());
    }

    }
}
