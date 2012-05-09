<?php
/**
 * Authentication source which selects an auth source based on a set of conditions.
 *
 * @author Peter Verhage, Egeniq
 * @package simpleSAMLphp
 * @version $Id$
 */
class sspmod_conditionalauth_Auth_Source_ConditionalAuth extends SimpleSAML_Auth_Source {

	/**
	 * The key of the AuthId field in the state.
	 */
	const AUTHID = 'sspmod_conditionalauth_Auth_Source_ConditionalAuth.AuthId';

	/**
	 * The string used to identify our states.
	 */
	const STAGEID = 'sspmod_conditionalauth_Auth_Source_ConditionalAuth.StageId';

	/**
	 * The key where the sources is saved in the state.
	 */
	const SOURCESID = 'sspmod_conditionalauth_Auth_Source_ConditionalAuth.SourceId';

	/**
	 * The key where the selected source is saved in the session.
	 */
	const SESSION_SOURCE = 'conditionalauth:selectedSource';

	/**
	 * Array of sources and conditions.
	 */
	private $sources;

	/**
	 * Constructor for this authentication source.
	 *
	 * @param array $info	 Information about this authentication source.
	 * @param array $config	 Configuration.
	 */
	public function __construct($info, $config) {
		assert('is_array($info)');
		assert('is_array($config)');

		/* Call the parent constructor first, as required by the interface. */
		parent::__construct($info, $config);

		if (!array_key_exists('sources', $config)) {
			throw new Exception('The required "sources" config option was not found');
		}

		$this->sources = $config['sources'];
	}

	/**
	 * Prompt the user with a list of authentication sources.
	 *
	 * This method saves the information about the configured sources,
	 * and redirects to a page where the user must select one of these
	 * authentication sources.
	 *
	 * This method never return. The authentication process is finished
	 * in the delegateAuthentication method.
	 *
	 * @param array &$state	 Information about the current authentication.
	 */
	public function authenticate(&$state) {
		assert('is_array($state)');
		
		$source = null;
		foreach ($this->sources as $name => $condition) {
		    if ($condition == null || $condition()) {
		        $source = $name;
		        break;
		    }
		}
		
		if ($source == null) {
            throw new Exception('No authentication source found that meets its condition(s)');		    
		}

		$as = SimpleSAML_Auth_Source::getById($source);
		if ($as === NULL) {
			throw new Exception('Invalid authentication source: ' . $source);
		}

		/* Save the selected authentication source for the logout process. */
		$session = SimpleSAML_Session::getInstance();
		$session->setData(self::SESSION_SOURCE, $this->authId, $source);

		try {
			$as->authenticate($state);
		} catch (SimpleSAML_Error_Exception $e) {
			SimpleSAML_Auth_State::throwException($state, $e);
		} catch (Exception $e) {
			$e = new SimpleSAML_Error_UnserializableException($e);
			SimpleSAML_Auth_State::throwException($state, $e);
		}
		SimpleSAML_Auth_Source::completeAuth($state);		
	}

	/**
	 * Log out from this authentication source.
	 *
	 * This method retrieves the authentication source used for this
	 * session and then call the logout method on it.
	 *
	 * @param array &$state	 Information about the current logout operation.
	 */
	public function logout(&$state) {
		assert('is_array($state)');

		/* Get the source that was used to authenticate */
		$session = SimpleSAML_Session::getInstance();
		$authId = $session->getData(self::SESSION_SOURCE, $this->authId);

		$source = SimpleSAML_Auth_Source::getById($authId);
		if ($source === NULL) {
			throw new Exception('Invalid authentication source during logout: ' . $source);
		}
		/* Then, do the logout on it */
		$source->logout($state);
	}

}