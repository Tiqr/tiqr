package org.tiqr.authenticator.exceptions;

/**
 * An exception that should be shown to the user in an alert dialog.
 * 
 * The exception uses a resource string as it's message.
 */
public class UserException extends Exception
{
    private static final long serialVersionUID = 2999071347338101165L;

    /**
     * Constructs a new user exception with the given message
     * 
     * @param message message
     */
    public UserException(String message) 
    {
        super(message);
    }

    /**
     * Constructs a new user exception with the given message
     * 
     * @param message message
     * @param parent  parent exception
     */    
    public UserException(String message, Throwable parent) 
    {
        super(message, parent);
    }
}
