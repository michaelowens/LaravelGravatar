<?php namespace Gravatar;

/**
 * Gravatar - A package based on Damian Bushong's GravatarLib (https://github.com/damianb/gravatarlib)
 *
 * @author		Michael John Owens (Original lib by Damian Bushong)
 * @copyright	(c) 2011 - Michael John Owens
 */

use System\Config;
use System\HTML;

class Gravatar
{
	/**
	 * @var integer - The size to use for avatars.
	 */
	protected static $size = 80;

	/**
	 * @var mixed - The default image to use - either a string of the gravatar-recognized default image "type" to use, a URL, or false if using the...default gravatar default image (hah)
	 */
	protected static $default_image = false;

	/**
	 * @var string - The maximum rating to allow for the avatar.
	 */
	protected static $max_rating = 'g';

	/**
	 * @var boolean - Should we use the secure (HTTPS) URL base?
	 */
	protected static $use_secure_url = false;

	/**
	 * @var string - A temporary internal cache of the URL parameters to use.
	 */
	protected static $param_cache = NULL;

	/**#@+
	 * @var string - URL constants for the avatar images
	 */
	const HTTP_URL = 'http://www.gravatar.com/avatar/';
	const HTTPS_URL = 'https://secure.gravatar.com/avatar/';
	/**#@-*/
	
	/**
	 * Get the currently set avatar size.
	 * @return integer - The current avatar size in use.
	 */
	public static function getAvatarSize( )
	{
		return ( Config::has( 'gravatar.size' ) ) ? Config::get( 'gravatar.size' ) : self::$size;
	}

	/**
	 * Get the current default image setting.
	 * @return mixed - False if no default image set, string if one is set.
	 */
	public static function getDefaultImage( )
	{
		return ( Config::has( 'gravatar.default_image' ) ) ? Config::get( 'gravatar.default_image' ) : self::$default_image;
	}

	/**
	 * Get the current maximum allowed rating for avatars.
	 * @return string - The string representing the current maximum allowed rating ('g', 'pg', 'r', 'x').
	 */
	public static function getMaxRating( )
	{
		return ( Config::has( 'gravatar.max_rating' ) ) ? Config::get( 'gravatar.max_rating' ) : self::$max_rating;
	}

	/**
	 * Check if we are using the secure protocol for the image URLs.
	 * @return boolean - Are we supposed to use the secure protocol?
	 */
	public static function usingSecureImages( )
	{
		return ( Config::has( 'gravatar.use_secure_url' ) ) ? Config::get( 'gravatar.use_secure_url' ) : self::$use_secure_url;
	}

	/**
	 * Build the avatar URL based on the provided email address.
	 * @param string $email - The email to get the gravatar for.
	 * @return string - The XHTML-safe URL to the gravatar.
	 */
	public static function buildGravatarURL( $email, $size = null, $force_secure = false )
	{
		// Start building the URL, and deciding if we're doing this via HTTPS or HTTP.
		if( self::usingSecureImages( ) || $force_secure )
		{
			$url = static::HTTPS_URL;
		}
		else
		{
			$url = static::HTTP_URL;
		}

		// Tack the email hash onto the end.
		$url .= self::getEmailHash( $email );

		// Check to see if the param_cache property has been populated yet
		if( self::$param_cache === NULL)
		{
			// Time to figure out our request params
			$params = array();
			$params[] = 's=' . ( is_null( $size ) ? self::getAvatarSize( ) : $size );
			$params[] = 'r=' . self::getMaxRating( );
			if( self::getDefaultImage( ) )
			{
				$params[] = 'd=' . self::getDefaultImage( );
			}

			// Stuff the request params into the param_cache property for later reuse
			self::$param_cache = ( !empty( $params ) ) ? '?' . implode( '&amp;', $params ) : '';
		}

		// And we're done.
		return $url . self::$param_cache;
	}

	/**
	 * Get the email hash to use (after cleaning the string).
	 * @param string $email - The email to get the hash for.
	 * @return string - The hashed form of the email, post cleaning.
	 */
	public static function getEmailHash( $email )
	{
		// Using md5 as per gravatar docs.
		return hash( 'md5', strtolower( trim( $email ) ) );
	}
	
	/**
	 * Get the gravatar
	 */
	public static function get( $email, $size = null )
	{
		return self::buildGravatarURL( $email, $size );
	}
	
	/**
	 * Get the gravatar with forced secure connection
	 */
	public static function get_secure( $email, $size = null )
	{
		return self::buildGravatarURL( $email, $size, true );
	}
	
	/**
	 * Get the gravatar and return as image
	 */
	public static function get_image( $email, $size = null )
	{
		return HTML::image( self::buildGravatarURL( $email, $size ) );
	}
	
	/**
	 * Get the gravatar with forced secure connection and return as image
	 */
	public static function get_secure_image( $email, $size = null )
	{
		return HTML::image( self::buildGravatarURL( $email, $size, true ) );
	}
	
}