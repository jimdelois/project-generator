<?php

namespace __TOKEN_NAMESPACE__\Test;

abstract class AbstractTestCase extends \PHPUnit_Framework_TestCase
{

    protected function getFullMock($class_name)
    {

        return $this->getMockBuilder($class_name)
            ->disableOriginalConstructor()
            ->getMock();
    }
}
