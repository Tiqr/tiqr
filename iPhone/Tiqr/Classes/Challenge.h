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

#import <CoreData/CoreData.h>

/**
 * Base class for the different challenges (authentication, enrollment) the
 * application needs to handle. Provides a somewhat abstract interface for
 * parsing a raw challenge.
 */
@interface Challenge : NSObject {

}

/**
 * The raw challenge retrieved from a QR code, URL handler or push notification.
 */
@property (nonatomic, copy, readonly) NSString *rawChallenge;

/**
 * Managed object context.
 */
@property (nonatomic, retain, readonly) NSManagedObjectContext *managedObjectContext;

/**
 * Can be used to check is the raw challenge could be parsed successfully.
 */
@property (nonatomic, assign, getter=isValid, readonly) BOOL valid;

/**
 * Contains an error message in case the challenge isn't valid.
 */
@property (nonatomic, retain, readonly) NSError *error;

/**
 * Constructs a new challenge object for the given raw challenge.
 *
 * Immediately calls the parseRawChallenge method.
 *
 * @param challenge the raw challenge
 * @param context   the managed object context
 *
 * @return challenge instance
 */
- (id)initWithRawChallenge:(NSString *)challenge managedObjectContext:(NSManagedObjectContext *)context;

/**
 * Constructs a new challenge object for the given raw challenge.
 *
 * @param challenge the raw challenge
 * @param context   the managed object context
 * @param autoParse automatically parse the challenge?
 *
 * @return challenge instance
 */
- (id)initWithRawChallenge:(NSString *)challenge managedObjectContext:(NSManagedObjectContext *)context autoParse:(BOOL)autoParse;

/**
 * Responsible for parsing the raw challenge.
 *
 * Sets the valid and errorMessage properties and any other property whoms
 * data can be retrieved based on the contents of the challenge.
 */
- (void)parseRawChallenge;

@end