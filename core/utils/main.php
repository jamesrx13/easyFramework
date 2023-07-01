<?php

function is_local()
{
    return $_SERVER['HTTP_HOST'] == "localhost" ? true : false;
}
