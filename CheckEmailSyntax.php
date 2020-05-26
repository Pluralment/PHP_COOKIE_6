<?php
function CheckEmailSyntax($email)
{
    if(preg_match("/^((?!\.)[\w\-_\.]*[^.])(@\w+)(\.\w+(\.\w+)?[^.\W])$/", $email))
        return true;
}