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
#import "Challenge-Protected.h"

@interface Challenge ()

@property (nonatomic, copy) NSString *rawChallenge;
@property (nonatomic, retain) NSManagedObjectContext *managedObjectContext;

@end

@implementation Challenge

@synthesize rawChallenge=rawChallenge_, managedObjectContext=managedObjectContext_, valid=valid_, error=error_;

- (id)initWithRawChallenge:(NSString *)challenge managedObjectContext:(NSManagedObjectContext *)context {
    return [self initWithRawChallenge:challenge managedObjectContext:context autoParse:YES];
}

- (id)initWithRawChallenge:(NSString *)challenge managedObjectContext:(NSManagedObjectContext *)context autoParse:(BOOL)autoParse {
	if ((self = [super init]) != nil) {
		self.rawChallenge = challenge;
        self.managedObjectContext = context;
        
        if (autoParse) {
            [self parseRawChallenge];
        }
	}
	
	return self;
}

- (NSString *)decodeURL:(NSString *)url {
	url = [url stringByReplacingOccurrencesOfString:@"+" withString:@" "];
	url = [url stringByReplacingPercentEscapesUsingEncoding:NSUTF8StringEncoding];
	return url;
}

- (void)parseRawChallenge {
}

- (BOOL)isValid {
    return self.error == nil;
}

- (void)dealloc {
    self.managedObjectContext = nil;
    self.rawChallenge = nil;
    self.error = nil;
    [super dealloc];
}

@end
