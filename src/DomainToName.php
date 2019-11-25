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
			curl_setopt($ch, CURLOPT_CONNECTTIMEOUT, 5000);
			curl_setopt($ch, CURLOPT_TIMEOUT, 5000);
			$content = curl_exec($ch);

			$http_status = curl_getinfo($ch, CURLINFO_HTTP_CODE);
			if ($http_status == 200) {
				$this->content = $content;
				$this->_exists = true;
			} else {
				echo("$domain without 200\n");
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
				$title = str_replace(" ", "", $title);

				$unwanted_array = array(    'Š'=>'S', 'š'=>'s', 'Ž'=>'Z', 'ž'=>'z', 'À'=>'A', 'Á'=>'A', 'Â'=>'A', 'Ã'=>'A', 'Ä'=>'A', 'Å'=>'A', 'Æ'=>'A', 'Ç'=>'C', 'È'=>'E', 'É'=>'E',
                            'Ê'=>'E', 'Ë'=>'E', 'Ì'=>'I', 'Í'=>'I', 'Î'=>'I', 'Ï'=>'I', 'Ñ'=>'N', 'Ò'=>'O', 'Ó'=>'O', 'Ô'=>'O', 'Õ'=>'O', 'Ö'=>'O', 'Ø'=>'O', 'Ù'=>'U',
                            'Ú'=>'U', 'Û'=>'U', 'Ü'=>'U', 'Ý'=>'Y', 'Þ'=>'B', 'ß'=>'Ss', 'à'=>'a', 'á'=>'a', 'â'=>'a', 'ã'=>'a', 'ä'=>'a', 'å'=>'a', 'æ'=>'a', 'ç'=>'c',
                            'è'=>'e', 'é'=>'e', 'ê'=>'e', 'ë'=>'e', 'ì'=>'i', 'í'=>'i', 'î'=>'i', 'ï'=>'i', 'ð'=>'o', 'ñ'=>'n', 'ò'=>'o', 'ó'=>'o', 'ô'=>'o', 'õ'=>'o',
                            'ö'=>'o', 'ø'=>'o', 'ù'=>'u', 'ú'=>'u', 'û'=>'u', 'ý'=>'y', 'þ'=>'b', 'ÿ'=>'y' );
				$title = strtr($title, $unwanted_array);
				$title = strtolower($title);
				$titleContent = strtr($titleContent, $unwanted_array);

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

		private function getFromTitleParsing($content) {
			$dom = $this->getDOM($content);
			$title = $dom->getElementsByTagName("title");
			if ($title) {
				$title = $title[0]->nodeValue;
			}
			if (strpos($title, "|") !== false) {
				return explode("|", $title)[0];
			} else if (strpos($title, "-") !== false) {
				return explode("-", $title)[0];
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
					} else {
						$name = $this->getFromTitleParsing($content);
						if ($name) {
							return $name;
						}
					}
				}
			}
		}

	}


?>
