<?php
class validation
{   
    private $sql;
    private $dbcon;
    private $currency;
    public function __construct()
    {
        $this->sql = new MYSQL_OPERATIONS();
        $this->dbcon = $GLOBALS['connection'];
    }
    
    public function set_rules($field, $label = '', $rules = '')
    {
        $error = array();
        if(is_array($field))
        {
            foreach($field as $value)
            {
                //form get or post field
                $form_field             =         $value['field'];
                //label
                $form_label         =         $value['label'];
                //all rules
                $form_rules         =         $value['rules'];
                $form_email            =        $value['email'];
                $form_min_length    =         $value['min_length'];
                $form_max_length    =        $value['max_length'];
                $form_matches        =        $value['matches'];
                $form_alpha_numeric    =        $value['alpha_numeric'];
                $form_numeric        =        $value['numeric'];
                $form_integer        =        $value['integer'];
                $form_decimal        =        $value['decimal'];
                $unique                =        $value['unique'];

                if($form_rules == 'need')
                {
                    if($this->required($form_field) == false)
                    {
                        $error[] = "The {$form_label} field is required";
                    }
                }

                if($form_email == 'valid')
                {
                    if($this->valid_emails($form_field) == false)
                    {
                        $error[] = "The {$form_label} field must contain a valid email address";
                    }
                }

                if($unique == true)
                {
                    $rules_exploed = explode('.',$unique);
                    if($this->unique($rules_exploed[0], $rules_exploed[1], $form_field) > 0)
                    {
                        $error[] = "The {$form_label} field must be unique.";
                    }
                }

                if($form_min_length == true)
                {
                    if($this->min_length($form_field, $form_min_length) == false)
                    {
                        $error[] = "The {$form_label} minimum length {$form_min_length} character.";
                    }
                }

                if($form_max_length == true)
                {
                    if($this->max_length($form_field, $form_max_length) == false)
                    {
                        $error[] = "The {$form_label} maximum length {$form_max_length} character.";
                    }
                }

                if($form_matches == true)
                {
                    if($this->matches($form_matches, $form_field) == false)
                    {
                        $error[] = "The {$form_label} field does not match the Password field.";
                    }
                }

                if($form_alpha_numeric == true)
                {
                    if($this->alpha_numeric($form_field) == false)
                    {
                        $error[] = "The {$form_label} field must be alpha numeric.";
                    }
                }

                if($form_numeric == true)
                {
                    if($this->numeric($form_field) == false)
                    {
                        $error[] = "The {$form_label} field must be numeric.";
                    }
                }

                if($form_integer == true)
                {
                    if($this->integer($form_field) == false)
                    {
                        $error[] = "The {$form_label} field must be integer.";
                    }
                }

                if($form_decimal == true)
                {
                    if($this->decimal($form_field) == false)
                    {
                        $error[] = "The {$form_label} field must be decimal.";
                    }
                }
            }
        }
        else
        {
            $rules_exploed = explode('|',$rules);
            for($x = 0; $x < count($rules_exploed); $x++)
            {
                $final_rules = $rules_exploed[$x];
                $rules_exploed2 = explode('[', $final_rules);


                if($final_rules == 'need')
                {
                    if($this->required($field) == false)
                    {
                        $error[] = "The {$label} field is required.";
                    }
                }

                if($final_rules == 'valid')
                {
                    if($this->valid_emails($field) == false)
                    {
                        $error[] = "The {$label} field must contain a valid email address.";
                    }
                }

                if($final_rules == 'integer')
                {
                    if($this->integer($field) == false)
                    {
                        $error[] = "The {$label} field must be integer.";
                    }
                }

                if($final_rules == 'alpha_numeric')
                {
                    if($this->alpha_numeric($field) == false)
                    {
                        $error[] = "The {$label} field must be alpha numeric.";
                    }
                }

                if($final_rules == 'decimal')
                {
                    if($this->decimal($field) == false)
                    {
                        $error[] = "The {$label} field must be decimal.";
                    }
                }

                if($final_rules == 'numeric')
                {
                    if($this->numeric($field) == false)
                    {
                        $error[] = "The {$label} field must be numeric.";
                    }
                }

                if($rules_exploed2[0] == 'min_length')
                {
                    $minlimit = str_ireplace(']', "", $rules_exploed2[1]);
                    if($this->min_length($field, $minlimit) == false)
                    {
                        $error[] = "The {$label} minimum length {$minlimit} character.";
                    }
                }

                if($rules_exploed2[0] == 'max_length')
                {
                    $maxlimit = str_ireplace(']', "", $rules_exploed2[1]);
                    if($this->max_length($field, $maxlimit) == false)
                    {
                        $error[] = "The {$form_label} maximum length {$maxlimit} character.";
                    }
                }
            }
        }

        if(count($error) > 0)
        {
            return $error;
        }
        else
        {
            return true;
        }
    }

    private function required($str)
    {
        if ( ! is_array($str))
        {
            return (trim($str) == '') ? FALSE : TRUE;
        }
        else
        {
            return ( ! empty($str));
        }
    }

    private function unique($table, $filed, $value)
    {
        $sql = "select  count(*) as total FROM `".$table."` where `".$filed."` = '".$value."'";
        $result = mysqli_query($this->dbcon, $sql);
        while($row = mysqli_fetch_array($result))
        {
            return $row['total'];
        }
    }

    private function matches($str, $field)
    {
        if(($str != $field))
        return false;
        else
        return true;
    }

    private function min_length($str, $val)
    {
        if (preg_match("/[^0-9]/", $val))
        {
            return FALSE;
        }

        if (function_exists('mb_strlen'))
        {
            return (mb_strlen($str) < $val) ? FALSE : TRUE;
        }

        return (strlen($str) < $val) ? FALSE : TRUE;
    }

    private function max_length($str, $val)
    {
        if (preg_match("/[^0-9]/", $val))
        {
            return FALSE;
        }

        if (function_exists('mb_strlen'))
        {
            return (mb_strlen($str) > $val) ? FALSE : TRUE;
        }

        return (strlen($str) > $val) ? FALSE : TRUE;
    }

    private function valid_email($str)
    {
        return ( ! preg_match("/^([a-z0-9\+_\-]+)(\.[a-z0-9\+_\-]+)*@([a-z0-9\-]+\.)+[a-z]{2,6}$/ix", $str)) ? FALSE : TRUE;
    }

    private function valid_emails($str)
    {
        if (strpos($str, ',') === FALSE)
        {
            return $this->valid_email(trim($str));
        }

        foreach (explode(',', $str) as $email)
        {
            if (trim($email) != '' && $this->valid_email(trim($email)) === FALSE)
            {
                return FALSE;
            }
        }
        return TRUE;
    }

    private function alpha_numeric($str)
    {
        return ( ! preg_match("/^([a-z0-9])+$/i", $str)) ? FALSE : TRUE;
    }

    private function numeric($str)
    {
        return (bool)preg_match( '/^[\-+]?[0-9]*\.?[0-9]+$/', $str);

    }

    private function integer($str)
    {
        return (bool) preg_match('/^[\-+]?[0-9]+$/', $str);
    }

    private function decimal($str)
    {
        return (bool) preg_match('/^[\-+]?[0-9]+\.[0-9]+$/', $str);
    }
    
    public function authenticate($auth_code, $device_id){
         $select="select admin_user_id  from tbl_admin_log where auth_code='$auth_code' and device_id='$device_id'";
         $result    =    $sql->execute_query($select);
         if(mysqli_num_rows($result)>0)
        {
            return true;
        }
    }
}
?>