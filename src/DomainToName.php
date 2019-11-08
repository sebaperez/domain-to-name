<?php

	class DomainToName {

		private $domain;
		private $_exists = false;
		private $content = "";

		public function __construct($domain) {
			$this->domain = $domain;
			$url = "http://$domain/";
			$ch = curl_init($url);
			curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
			curl_setopt($ch, CURLOPT_FOLLOWLOCATION, true);
			curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, false);
			curl_setopt($ch, CURLOPT_SSL_VERIFYHOST, false);
			curl_setopt($ch, CURLOPT_USERAGENT, "Mozilla/5.0 (iPhone; CPU iPhone OS 8_2 like Mac OS X) AppleWebKit/600.1.4 (KHTML, like Gecko) GSA/5.2.43972 Mobile/12D508 Safari/600.1.4");
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

		private function getFromOGTag($content) {
			$dom = $this->getDOM($content);
			$metas = $dom->getElementsByTagName("meta");
			foreach ($metas as $meta) {
				if ($meta->getAttribute("property") == "og:site_name") {
					return $meta->getAttribute("content");
				}
			}
		}

		private function getChildDomainName() {
			return strtolower(explode(".", $this->getDomain())[0]);
		}

		private function getFromTitle($content) {
			$dom = $this->getDOM($content);
			$title = $dom->getElementsByTagName("title");
			if ($title) {
				$title = $title[0]->nodeValue;
				$titleContent = $title;
				$title = strtolower($title);
				$title = str_replace(" ", "", $title);
				$domainName = $this->getChildDomainName();
				if (strpos($title, $domainName) !== false) {
					$pattern = implode("\\s*", str_split($domainName));
					preg_match("/$pattern/i", $titleContent, $result);
					if ($result && isset($result[0])) {
						return $result[0];
					}
				}
			}
		}

		public function getName() {
			$content = $this->getContent();
			if ($this->exists() && $content) {
				$name = $this->getFromOGTag($content);
				if ($name) {
					return $name;
				} else {
					$name = $this->getFromTitle($content);
					if ($name) {
						return $name;
					}
				}
			}
		}

	}


?>
