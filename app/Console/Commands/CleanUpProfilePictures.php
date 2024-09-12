<?php

namespace App\Console\Commands;

use App\Models\User;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\File;

class CleanUpProfilePictures extends Command
{

    protected $signature   = 'app:clean-up-profilepictures';
    protected $description = 'Clean up profile pictures that somehow didn\'t get deleted when the user was deleted';

    public function handle(): void {
        $usedPictures = User::select('avatar')->distinct()->get()->pluck('avatar')->filter()->toArray();

        $profilePictures = new \FilesystemIterator(public_path("uploads/avatars"));
        foreach ($profilePictures as $profilePicture) {
            if (!in_array(basename($profilePicture), $usedPictures)) {
                File::delete($profilePicture);
                $this->info('Deleted profile picture ' . basename($profilePicture));
            }
        }
    }
}
