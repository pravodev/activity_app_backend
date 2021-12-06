<?php

namespace App\Rules;

use Illuminate\Contracts\Validation\Rule;

class SpeedrunRule implements Rule
{
    public $type;
    
    /**
     * Create a new rule instance.
     *
     * @return void
     */
    public function __construct($type)
    {
        $this->type = $type;
    }

    /**
     * Determine if the validation rule passes.
     *
     * @param  string  $attribute
     * @param  mixed  $value
     * @return bool
     */
    public function passes($attribute, $value)
    {
        switch ($this->type) {
            case 'value':
                return is_numeric($value);
            case 'speedrun':
                // format example : 1h 34m 33s 00ms
                $split = explode(' ', $value);

                if(count($split) != 4) {
                    return false;
                }

                if(!strpos($split[0],'h') || 
                    !strpos($split[1],'m') ||
                    !strpos($split[2],'s') ||
                    !strpos($split[3],'ms')) {
                    return false;
                }

                foreach($split as $i => $value) {
                    preg_match_all('!\d+!', $value, $matches);
                    $number = $matches[0][0] ?? null;

                    if($i == count($split)-1) {
                        if($number > 999) {
                            return false;
                        }
                        continue;
                    } else {
                        if($number > 59) {
                            return false;
                        }
                    }
                    
                }

                return true;
            default:
                return true;
        }

    }

    /**
     * Get the validation error message.
     *
     * @return string
     */
    public function message()
    {
        switch ($this->type) {
            case 'value':
                return 'The value must be a number';
            case 'speedrun':
                return 'Time as target must be in the format: 1h 34m 33s 00ms';
        }
    }
}
