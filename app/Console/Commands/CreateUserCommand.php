<?php

namespace App\Console\Commands;

use App\Models\User;
use Illuminate\Console\Command;
use Illuminate\Support\Str;

class CreateUserCommand extends Command
{
    protected $signature = 'create:user {name} {email} {password?}';
    // protected $signature = 'create:user {--verified} {--name=} {--email=} {--password=}';

    protected $description = 'Create a New User';

    public function handle()
    {
        // $name = Str::random(8);
        // $name = Str::lower($name);
        // $password = Str::random(12);

        $name = $this->argument('name');
        $email = $this->argument('email');
        $password = $this->argument('password') ?? Str::random(12);

        // $name = $this->option('name');
        // $email = $this->option('email');
        // $password = $this->option('password') ?? Str::random(12);

        User::create([
            'name' => $name,
            // 'email' => $name. '@gmail.com',
            'email' => $email,
            'password' => bcrypt($password),
            'email_verified_at' => $this->option('verified') ? now() : null,
        ]);
        $this->info('Successfully created. Email: ' . $email . '; Password: ' . $password);
    }
}
