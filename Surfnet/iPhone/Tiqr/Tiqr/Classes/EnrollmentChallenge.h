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
extern NSString *const TIQRECErrorDomain;

enum {
    TIQRECUnknownError = 101,    
    TIQRECIdentityProviderLogoError = 201,
    TIQRECAccountAlreadyExistsError = 202,
    TIQRECInvalidQRTagError = 203,
    TIQRECConnectionError = 204,
    TIQRECInvalidResponseError = 205
};

/**
 * Enrollment challenge helper object.
 *
 * Parses the raw enrollment challenge and makes properties
 * available containing the identity provider and identity properties etc.
 
 * Will use extra synchronous HTTP requests to retrieve the full 
 * enrollment details, identity provider logo etc. Make sure you instantiate
 * this class in a separate thread.
 */
@interface EnrollmentChallenge : Challenge {

}

- (id)initWithRawChallenge:(NSString *)challenge managedObjectContext:(NSManagedObjectContext *)context allowFiles:(BOOL)allowFiles;

/**
 * Initialize the enrollment challenge handler.
 *
 * Doesn't allow local files.
 *
 * @param challenge  the raw challenge
 * @param context    the managed object context
 * @param allowFiles allow local files for the enrollment details?
 *
 * @return EnrollmentChallenge
 */
- (id)initWithRawChallenge:(NSString *)challenge managedObjectContext:(NSManagedObjectContext *)context;


/**
 * Initialize the enrollment challenge handler.
 *
 * Doesn't allow local files for the enrollment details
 *
 * @param challenge the raw challenge
 * @param context   the managed object context
 *
 * @return EnrollmentChallenge
 */
- (id)initWithRawChallenge:(NSString *)challenge managedObjectContext:(NSManagedObjectContext *)context;    

/**
 * Identity provider identifier.
 */
@property (nonatomic, copy, readonly) NSString *identityProviderIdentifier;

/**
 * Identity provider display name.
 */
@property (nonatomic, copy, readonly) NSString *identityProviderDisplayName;

/**
 * Identity provider authentication URL.
 */
@property (nonatomic, copy, readonly) NSString *identityProviderAuthenticationUrl;

/**
 * Identity provider info URL.
 */
@property (nonatomic, copy, readonly) NSString *identityProviderInfoUrl;

/**
 * The OCRA suite the identity provider is using.
 */
@property (nonatomic, copy, readonly) NSString *identityProviderOcraSuite;

/**
 * Binary data for the identity provider logo.
 */
@property (nonatomic, copy, readonly) NSData *identityProviderLogo;

/**
 * The existing identity provider the matches the provider in the enrollment
 * challenge. Only set if the provider is already known.
 */
@property (nonatomic, retain) IdentityProvider *identityProvider;

/**
 * Identity identifier for the user.
 */
@property (nonatomic, copy, readonly) NSString *identityIdentifier;

/**
 * Identity display name.
 */
@property (nonatomic, copy, readonly) NSString *identityDisplayName;

/**
 * Identity secret, initially not set, will be set during
 * the enrollment process.
 */
@property (nonatomic, copy) NSData *identitySecret;

/**
 * Identity PIN, initially not set, will be set during
 * the enrollment process.
 */
@property (nonatomic, copy) NSString *identityPIN;

/**
 * Identity, in case of account reactivation or at the
 * end of the activation process.
 */
@property (nonatomic, retain) Identity *identity;

/**
 * One-time enrollment URL.
 */
@property (nonatomic, copy, readonly) NSString *enrollmentUrl;

/**
 * Return URL, if invoked using an URL handler.
 */
@property (nonatomic, copy, readonly) NSString *returnUrl;

@end