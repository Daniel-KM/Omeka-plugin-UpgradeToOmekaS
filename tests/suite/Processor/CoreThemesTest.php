<?php

class UpgradeToOmekaS_Processor_CoreThemesTest extends UpgradeToOmekaS_Test_AppTestCase
{
    protected $_isAdminTest = true;

    public function setUp()
    {
        parent::setUp();

        // Authenticate and set the current user.
        $this->user = $this->db->getTable('User')->find(1);
        $this->_authenticateUser($this->user);
    }
    public function testCopyThemes()
    {
        $this->markTestIncomplete();
    }
}
