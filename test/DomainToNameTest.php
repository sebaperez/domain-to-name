<?php

	require("src/DomainToName.php");
	use PHPUnit\Framework\TestCase;

	class DomainToNameTest extends TestCase {

		public function test_site_exists() {
			$domaintoname = new DomainToName("google.com");
			$this->assertTrue($domaintoname->exists());
		}

		public function test_site_does_not_exists() {
			$domaintoname = new DomainToName("this.is.an.invalid.site");
			$this->assertFalse($domaintoname->exists());
		}

		public function test_get_name_from_ogtag() {
			$domaintoname = new DomainToName("sheraton.marriott.com");
			$this->assertTrue($domaintoname->exists());
			$this->assertEquals("Sheraton Hotels & Resorts", $domaintoname->getName());
		}

		public function test_get_name_from_title() {
			$domaintoname = new DomainToName("motobuykers.es");
			$this->assertTrue($domaintoname->exists());
			$this->assertEquals("Motobuykers", $domaintoname->getName());
		}

	}

?>
