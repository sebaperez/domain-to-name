<?php

	class DomainToName {

		private $domain;
		private $_exists = false;
		private $content = "";

		public function __construct($domain) {
			$this->domain = $domain;
			$url = "http://" . $this->getDomain();
			$ch = curl_init($url);
			curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
			curl_setopt($ch, CURLOPT_FOLLOWLOCATION, true);
			curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, false);
			curl_setopt($ch, CURLOPT_SSL_VERIFYHOST, false);
			$content = curl_exec($ch);

			$http_status = curl_getinfo($ch, CURLINFO_HTTP_CODE);
			if ($http_status == 200) {
				$this->content = $content;
				$this->_exists = true;
			} else {
				$this->content = "";
				$this->_exists = false;
			}
		}

		public function getDomain() {
			return $this->domain;
		}

		public function exists() {
			return $this->_exists;
		}

		private function getContent() {
			return $this->content;
		}

		private function getDOM($content) {
			$dom = new DOMDocument;
			@$dom->loadHTML($content);
			return $dom;
		}

		public function getFromOGTag($content) {
			$dom = $this->getDOM($content);
			$metas = $dom->getElementsByTagName("meta");
			foreach ($metas as $meta) {
				if ($meta->getAttribute("property") == "og:site_name") {
					return $meta->getAttribute("content");
				}
			}
		}

		public function getChildDomainName() {
			return strtolower(explode(".", $this->getDomain())[0]);
		}

		public function getFromTitle($content) {
			$dom = $this->getDOM($content);
			$title = $dom->getElementsByTagName("title");
			if ($title) {
				$title = $title[0];
				$title = strtolower($title);
				$title = str_replace(" ", $title);
				$domainName = $this->getChildDomainName();
				if (strpos($title, $domainName) !== false) {
					
				}
			}
		}

		public function getName() {
			$content = $this->getContent();
			if ($this->exists() && $content) {
				if ($name = $this->getFromOGTag($content)) {
					return $name;
				} else if ($name = $this->getFromTitle($content)) {
					return $name;
				}
			}
		}

	}


?>
