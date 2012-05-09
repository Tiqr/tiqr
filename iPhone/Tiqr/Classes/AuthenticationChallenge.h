/*
 * Copyright (c) 2010-2011 SURFnet bv
 * All rights reserved.
 *
 * Redistribution and use in source and binary forms, with or without
 * modification, are permitted provided that the following conditions
 * are met:
 * 1. Redistributions of source code must retain the above copyright
 *    notice, this list of conditions and the following disclaimer.
 * 2. Redistributions in binary form must reproduce the above copyright
 *    notice, this list of conditions and the following disclaimer in the
 *    documentation and/or other materials provided with the distribution.
 * 3. Neither the name of SURFnet bv nor the names of its contributors 
 *    may be used to endorse or promote products derived from this 
 *    software without specific prior written permission.
 *
 * THIS SOFTWARE IS PROVIDED BY THE AUTHOR ``AS IS'' AND ANY EXPRESS OR
 * IMPLIED WARRANTIES, INCLUDING, BUT NOT LIMITED TO, THE IMPLIED
 * WARRANTIES OF MERCHANTABILITY AND FITNESS FOR A PARTICULAR PURPOSE
 * ARE DISCLAIMED. IN NO EVENT SHALL THE AUTHOR BE LIABLE FOR ANY
 * DIRECT, INDIRECT, INCIDENTAL, SPECIAL, EXEMPLARY, OR CONSEQUENTIAL
 * DAMAGES (INCLUDING, BUT NOT LIMITED TO, PROCUREMENT OF SUBSTITUTE
 * GOODS OR SERVICES; LOSS OF USE, DATA, OR PROFITS; OR BUSINESS
 * INTERRUPTION) HOWEVER CAUSED AND ON ANY THEORY OF LIABILITY, WHETHER
 * IN CONTRACT, STRICT LIABILITY, OR TORT (INCLUDING NEGLIGENCE OR
 * OTHERWISE) ARISING IN ANY WAY OUT OF THE USE OF THIS SOFTWARE, EVEN
 * IF ADVISED OF THE POSSIBILITY OF SUCH DAMAGE.
 */

#import "Challenge.h"
#import "IdentityProvider.h"
#import "Identity.h"

/**
 * Error domain.
 */
extern NSString *const TIQRACErrorDomain;

enum {
    TIQRACUnknownError = 101,    
    TIQRACInvalidQRTagError = 201,
    TIQRACUnknownIdentityProviderError = 202,
    TIQRACUnknownIdentityError = 203,
    TIQRACZeroIdentitiesForIdentityProviderError = 204,
    TIQRACIdentityBlockedError = 205    
};

/**
 * Authentication challenge helper object.
 *
 * Parses the raw authentication challenge and makes properties
 * available containing the identity provider, identity (or multiple identities
 * if more than one identity matches), session key etc.
 */
@interface AuthenticationChallenge : Challenge {
	
}

/**
 * Identity provider.
 */
@property (nonatomic, retain, readonly) IdentityProvider *identityProvider;

/**
 * Identity (might be nil if more than one match).
 */
@property (nonatomic, retain) Identity *identity;

/**
 * Matching identities.
 */
@property (nonatomic, retain, readonly) NSArray *identities;

/**
 * The service provider identifier (probably domain name).
 */
@property (nonatomic, copy, readonly) NSString *serviceProviderIdentifier;

/**
 * The display name for the service provider.
 */
@property (nonatomic, copy, readonly) NSString *serviceProviderDisplayName;

/**
 * Session key.
 */
@property (nonatomic, copy, readonly) NSString *sessionKey;

/**
 * The authentication challenge.
 */
@property (nonatomic, copy, readonly) NSString *challenge;

/**
 * Optional return URL (if invoked from an URL).
 */
@property (nonatomic, copy, readonly) NSString *returnUrl;

@end