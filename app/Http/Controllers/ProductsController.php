<?php

namespace App\Http\Controllers;

class ProductsController extends SnsBaseController
{
    protected $topicName = "Products";

    function validation($message) {

        if(($message == "") or (!isset($message)) or ($message == '[""]')){

            return false;

        }

        return true;
    }
}
