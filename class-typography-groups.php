<?php

class Typography_Groups {

	public function __construct() {
		$this->settings = new \PHP_Typography\Settings();

		$this->typo = new \PHP_Typography\PHP_Typography();
	}

	/**
	 * Processes a text fragment.
	 *
	 * @since 3.2.4 Parameter $force_feed added.
	 * @since 4.0.0 Parameter $settings added.
	 * @since 5.3.0 The method can be called both statically and dynamically.
	 *
	 * @param string        $text       Required.
	 * @param bool          $is_title   Optional. Default false.
	 * @param bool          $force_feed Optional. Default false.
	 * @param Settings|null $settings   Optional. A settings object. Default null (which means the internal settings will be used).
	 *
	 * @return string The processed $text.
	 */
	public function process( $text, $is_title = false, $force_feed = false, Settings $settings = null ) {
		if(!$settings) $settings = $this->settings;

		return $this->typo->process_textnodes( $text, function( $DomText, $settings, $is_title ) {
			$DomParent = $DomText->parentNode;
			$DomDoc    = $DomText->ownerDocument;

			$wrapperClass = 'avoidwrap';

			$delims = array(
				'New York',
				'Apple iPhone'
			);

			$delims[] = ' '; // empty space for words in general (that didn't match the previous fragments)

			$fragments  = preg_split(
				'{(' . join('|', array_map('preg_quote', $delims)) . ')}',
				trim($DomText->textContent),
				-1,
				PREG_SPLIT_DELIM_CAPTURE
			);

			$fragmentNo = 0;
			$changed    = false;
			foreach($fragments as $fragment) {
				$fragmentNo++;

				if(trim($fragment) === '') continue; // skip whitspace/empty fragments

				$DomWord = $DomDoc->createElement('span');
				$DomWord->appendChild($DomDoc->createTextNode($fragment)); // handles escaping

				$DomWord->setAttribute('class', $wrapperClass);

				$DomParent->insertBefore(  $DomWord, $DomText  );
				$changed = true;

				// space between spans
				if($fragmentNo >= count($fragments)) continue; // skip last fragment
				$DomSpace = $DomDoc->createTextNode(' ');
				self::domInsertAfter(  $DomSpace, $DomWord  );
			}
			if($changed) $DomParent->removeChild($DomText); // clean up
		}, $settings, $is_title, $this->body_classes );
	}
	

	/** Inserts a new node after a given reference node. Basically it is the complement to the DOM specification's
	 * insertBefore() function.
	 * @see https://gist.github.com/deathlyfrantic/cd8d7ef8ba91544cdf06
	 * @param \DOMNode $newNode The node to be inserted.
	 * @param \DOMNode $referenceNode The reference node after which the new node should be inserted.
	 * @return \DOMNode The node that was inserted.
	 */
	public static function domInsertAfter(\DOMNode $newNode, \DOMNode $referenceNode)
	{
	  if($referenceNode->nextSibling === null) {
		  return $referenceNode->parentNode->appendChild($newNode);
	  } else {
		  return $referenceNode->parentNode->insertBefore($newNode, $referenceNode->nextSibling);
	  }
	}
	

	/**
	 * Processes a heading text fragment.
	 *
	 * Calls `process( $text, true, $settings )`.
	 *
	 * @since 4.0.0 Parameter $settings added.
	 *
	 * @param string        $text Required.
	 * @param Settings|null $settings Optional. A settings object. Default null (which means the internal settings will be used).
	 */
	public function process_title( $text, Settings $settings = null ) {
		return $this->process( $text, true, false, $settings );
	}

	/**
	 * Processes title parts and strips &shy; and zero-width space.
	 *
	 * @since 3.2.5
	 * @since 4.0.0 Parameter $settings added.
	 * @since 5.3.0 The method can be called both statically and dynamically.
	 *
	 * @param array         $title_parts An array of strings.
	 * @param Settings|null $settings    Optional. A settings object. Default null (which means the internal settings will be used).
	 *
	 * @return array
	 */
	public function process_title_parts( $title_parts, Settings $settings = null ) {
		foreach ( $title_parts as $index => $part ) {
			$title_parts[ $index ] = strip_tags(
				$this->process( $part, true, true, $settings )
			);
		}
		return $title_parts;
	}

	/**
	 * Processes a heading text fragment as part of an RSS feed.
	 *
	 * Calls `process_feed( $text, true, $settings )`.
	 *
	 * @since 5.3.0
	 *
	 * @param string        $text Required.
	 * @param Settings|null $settings Optional. A settings object. Default null (which means the internal settings will be used).
	 */
	public function process_feed_title( $text, Settings $settings = null ) {
		return $this->process_feed( $text, true, $settings );
	}

	/**
	 * Processes a content text fragment as part of an RSS feed.
	 *
	 * Calls `process( $text, $is_title, true )`.
	 *
	 * @since 3.2.4
	 * @since 4.0.0 Parameter $settings added.
	 * @since 5.3.0 The method can be called both statically and dynamically.
	 *
	 * @param string        $text     Required.
	 * @param bool          $is_title Optional. Default false.
	 * @param Settings|null $settings Optional. A settings object. Default null (which means the internal settings will be used).
	 */
	public function process_feed( $text, $is_title = false, Settings $settings = null ) {
		return $this->process( $text, $is_title, true, $settings );
	}

	/**
	 * Grabs the body classes from the filter hook.
	 *
	 * @param  string[] $classes An array of CSS classes.
	 *
	 * @return string[]
	 */
	public function filter_body_class( array $classes ) {
		$this->body_classes = $classes;
		return $classes;
	}
}
