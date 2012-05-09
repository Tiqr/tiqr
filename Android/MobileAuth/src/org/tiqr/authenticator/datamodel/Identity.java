package org.tiqr.authenticator.datamodel;

/**
 * Identity wrapper class for identities.
 */
public class Identity
{
    private long _id = -1;
    private String _identifier;
    private String _displayName;
    private int _sortIndex = 0;
    private boolean _blocked = false;
    
    /**
     * Is this a new service?
     * 
     * @return boolean is new? 
     */
    public boolean isNew()
    {
        return _id == -1;
    }
    
    /**
     * Returns the identity (row) id.
     * 
     * The id is -1 for an identity that hasn't bee inserted yet.
     * 
     * @return identity id
     */
    public long getId()
    {
        return _id;
    }
    
    /**
     * Sets the identity row id.
     * 
     * @param id row id
     */
    public void setId(long id)
    {
        _id = id;
    }
    
    /**
     * Returns the service identifier.
     * 
     * @return service identifier
     */
    public String getIdentifier()
    {
        return _identifier;
    }
    
    /**
     * Sets the identifier.
     * 
     * @param identifier identifier
     */
    public void setIdentifier(String identifier)
    {
        _identifier = identifier;
    }
    
    /**
     * Returns the display name.
     * 
     * @return display name
     */
    public String getDisplayName()
    {
        return _displayName;
    }
    
    /**
     * Sets the display name.
     * 
     * @param displayName display name
     */
    public void setDisplayName(String displayName)
    {
        _displayName = displayName;
    }    
    
    /**
     * Returns the sort index.
     * 
     * @return sort index
     */
    public int getSortIndex()
    {
        return _sortIndex;
    }
    
    /**
     * Sets the sort index.
     * 
     * @param sortIndex sort index
     */
    public void setSortIndex(int sortIndex)
    {
        _sortIndex = sortIndex;
    }

	/**
	 * Block (or unblock) a user
	 * 
	 * @param boolean blocked or not
	 */
	public void setBlocked(boolean blocked) {
		_blocked = blocked;
	}

	/**
	 * Whether the user is blocked or not
	 * 
	 * @return boolean blocked or not 
	 */
	public boolean isBlocked() {
		return _blocked;
	}    
}