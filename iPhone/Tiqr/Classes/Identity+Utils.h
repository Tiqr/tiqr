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

#import "Identity.h"
#import "IdentityProvider.h"

/**
 * Category which adds some useful utilities to the Identity class.
 */
@interface Identity (Utils) 

/**
 * Returns the number of identities in the given managed object context.
 *
 * @param context the managed object context
 *
 * @return number of identities
 */
+ (NSUInteger)countInManagedObjectContext:(NSManagedObjectContext *)context;

/**
 * Returns the maximum identity sort index in the given managed object context.
 *
 * @param context the managed object context
 *
 * @return maximum sort index
 */ 
+ (NSUInteger)maxSortIndexInManagedObjectContext:(NSManagedObjectContext *)context;

/**
 * Returns whether all identities are currently blocked or not.
 *
 * @param context the managed object context
 *
 * @return all identities blocked?
 */
+ (BOOL)allIdentitiesBlockedInManagedObjectContext:(NSManagedObjectContext *)context;

/**
 * Searches for an identity with the given identifier for the given identity provider.
 *
 * @param identifier         identity identifier
 * @param identityProvider   identity provider
 * @param context            managed object context
 *
 * @return identity
 */
+ (Identity *)findIdentityWithIdentifier:(NSString *)identifier forIdentityProvider:(IdentityProvider *)identityProvider inManagedObjectContext:(NSManagedObjectContext *)context;

/**
 * Returns all the identities for the given provider.
 *
 * @param identityProvider identity provider
 * @param context          managed object context
 *
 * @return list of identities
 */
+ (NSArray *)findIdentitiesForIdentityProvider:(IdentityProvider *)identityProvider inManagedObjectContext:(NSManagedObjectContext *)context;

/**
 * Blocks all identities. 
 *
 * NOTE: this method does not call save on the managed object context!
 */
+ (void)blockAllIdentitiesInManagedObjectContext:(NSManagedObjectContext *)context;

@end