<?php

class MyClass
{
    public function getSomeValue() {} // Pass
    public function get_some_value() {} // Fail
    public function _get_some_value() {} // Fail
    public function __get_some_value() {} // Fail
    public function __Get_some_value() {} // Fail
}
?>
