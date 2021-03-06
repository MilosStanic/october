<?php namespace Backend\Models;

use Mail;
use Backend;
use October\Rain\Auth\Models\User as UserBase;

/**
 * Administrator user model
 *
 * @package october\backend
 * @author Alexey Bobkov, Samuel Georges
 */
class User extends UserBase
{
    /**
     * @var string The database table used by the model.
     */
    protected $table = 'backend_users';

    /**
     * Validation rules
     */
    public $rules = [
        'login' => 'required|between:2,24|unique:backend_users',
        'email' => 'required|between:3,64|email|unique:backend_users',
        'password' => 'required:create|between:2,32|confirmed',
        'password_confirmation' => 'required_with:password|between:2,32'
    ];

    /**
     * Relations
     */
    public $belongsToMany = [
        'groups' => ['Backend\Models\UserGroup', 'table' => 'backend_users_groups']
    ];

    public $attachOne = [
        'avatar' => ['System\Models\File']
    ];

    /**
     * Purge attributes from data set.
     */
    protected $purgeable = ['password_confirmation', 'send_invite'];

    protected static $loginAttribute = 'login';

    /**
     * @return string Returns the user's full name.
     */
    public function getFullNameAttribute()
    {
        return trim($this->first_name . ' ' . $this->last_name);
    }

    /**
     * Gets a code for when the user is persisted to a cookie or session which identifies the user.
     * @return string
     */
    public function getPersistCode()
    {
        // Option A: @todo config
        // return parent::getPersistCode();

        // Option B:
        if (!$this->persist_code)
            return parent::getPersistCode();

        return $this->persist_code;
    }

    /**
     * Returns the public image file path to this user's avatar.
     */
    public function getAvatarThumb($size = 25, $default = null)
    {
        if ($this->avatar)
            return $this->avatar->getThumb($size, $size);
        else
            return '//www.gravatar.com/avatar/' . md5(strtolower(trim($this->email))) . '?s='.$size.'&d='.urlencode($default);
    }

    public function afterCreate()
    {
        $this->restorePurgedValues();

        if ($this->send_invite)
            $this->sendInvitation();
    }

    public function sendInvitation()
    {
        $data = [
            'name' => $this->full_name,
            'login' => $this->login,
            'password' => $this->getOriginalHashValue('password'),
            'link' => Backend::url('backend'),
        ];

        Mail::send('backend::mail.invite', $data, function($message)
        {
            $message->to($this->email, $this->full_name);
        });
    }

}
