<?php


namespace App\Service;

class GenericWordpressDOMParser extends AbstractDOMParser {

	public function isApplicableForUrl(string $url): bool {
		return true;
	}

	protected function fetchImages(\DOMDocument $doc): array {
		return $this->getXpathNodeAttributes($doc, '//div[@class="entry"]//img', 'src');
	}

	protected function fetchDescription(\DOMDocument $doc): string {
		return $this->getXpathNodeValue($doc, '//div[@class="entry"]');
	}

	protected function fetchTitle(\DOMDocument $doc): string {
		return $this->getXpathNodeValue($doc, '//h2[@class="pagetitle"]');
	}

	protected function fetchIngredients(\DOMDocument $doc): array {
		return [];
	}

	/**
	 * @param \DOMDocument $doc
	 * @param $xpathSelector
	 * @return string
	 */
	protected function getXpathNodeValue(\DOMDocument $doc, $xpathSelector): string {
		$xpath = new \DOMXPath($doc);
		$description = '';
		/** @var \DOMElement $node */
		foreach ($xpath->query($xpathSelector) as $node) {
			$description .= $node->nodeValue."\r\n";
		}
		return $description;
	}

	protected function getXpathNodeAttributes(\DOMDocument $doc, string $xpathSelector, string $attributeName): array {
		$xpath = new \DOMXPath($doc);
		$result = [];
		/** @var \DOMElement $node */
		foreach ($xpath->query($xpathSelector) as $node) {
			$result[] = $node->getAttribute($attributeName);
		}
		return $result;
	}
}
