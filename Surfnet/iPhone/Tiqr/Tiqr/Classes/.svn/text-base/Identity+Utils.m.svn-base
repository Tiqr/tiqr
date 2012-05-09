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

#import "Identity+Utils.h"

@implementation Identity (Utils)

+ (NSUInteger)countInManagedObjectContext:(NSManagedObjectContext *)context {
	NSFetchRequest *request = [[NSFetchRequest alloc] init];
	NSEntityDescription *entity = [NSEntityDescription entityForName:@"Identity" inManagedObjectContext:context];
	[request setEntity:entity];
	
	NSError *error = nil;
	NSUInteger count = [context countForFetchRequest:request error:&error];
	
    [request release];	
	
	return error == nil ? count : 0;
}

+ (NSUInteger)maxSortIndexInManagedObjectContext:(NSManagedObjectContext *)context {
	NSFetchRequest *request = [[NSFetchRequest alloc] init];
	NSEntityDescription *entity = [NSEntityDescription entityForName:@"Identity" inManagedObjectContext:context];
	[request setEntity:entity];
	
	NSExpression *keyPathExpression = [NSExpression expressionForKeyPath:@"sortIndex"];
	NSExpression *maxExpression = [NSExpression expressionForFunction:@"max:" arguments:[NSArray arrayWithObject:keyPathExpression]];
	
	NSExpressionDescription *expressionDescription = [[NSExpressionDescription alloc] init];
	[expressionDescription setName:@"maxSortIndex"];
	[expressionDescription setExpression:maxExpression];
	[expressionDescription setExpressionResultType:NSInteger16AttributeType];
	
	[request setResultType:NSDictionaryResultType];
	[request setPropertiesToFetch:[NSArray arrayWithObject:expressionDescription]];
	
	NSError *error = nil;
	NSArray *objects = [context executeFetchRequest:request error:&error];
	NSUInteger result = 0;
	if (objects != nil && [objects count] > 0) {
		result = [[[objects objectAtIndex:0] valueForKey:@"maxSortIndex"] intValue];
	}
	
    [expressionDescription release];
    [request release];	
	
	return result;
}

+ (BOOL)allIdentitiesBlockedInManagedObjectContext:(NSManagedObjectContext *)context {
    if ([Identity countInManagedObjectContext:context] == 0) {
        return NO;
    }
    
	NSFetchRequest *request = [[NSFetchRequest alloc] init];
	NSEntityDescription *entity = [NSEntityDescription entityForName:@"Identity" inManagedObjectContext:context];
	[request setEntity:entity];
	
	NSPredicate *predicate = [NSPredicate predicateWithFormat:@"blocked = %@", [NSNumber numberWithBool:NO]];
	[request setPredicate:predicate];
    
	NSError *error = nil;
	NSUInteger count = [context countForFetchRequest:request error:&error];
    [request release];	
	
	return error == nil && count == 0;
}

+ (Identity *)findIdentityWithIdentifier:(NSString *)identifier forIdentityProvider:(IdentityProvider *)identityProvider inManagedObjectContext:(NSManagedObjectContext *)context {
	NSEntityDescription *entityDescription = [NSEntityDescription entityForName:@"Identity" inManagedObjectContext:context];
	NSFetchRequest *request = [[NSFetchRequest alloc] init];
	[request setEntity:entityDescription];
	
	NSPredicate *predicate = [NSPredicate predicateWithFormat:@"identifier = %@ AND identityProvider = %@", identifier, identityProvider];
	[request setPredicate:predicate];
	
	NSError *error = nil;
	NSArray *result = [context executeFetchRequest:request error:&error];
	[request release];	
	
	Identity *identity = nil;
	if (result != nil && [result count] == 1) {
		identity = [result objectAtIndex:0];
	}
	
	return identity;
}

+ (NSArray *)findIdentitiesForIdentityProvider:(IdentityProvider *)identityProvider inManagedObjectContext:(NSManagedObjectContext *)context  {
	NSEntityDescription *entityDescription = [NSEntityDescription entityForName:@"Identity" inManagedObjectContext:context];
	NSFetchRequest *request = [[NSFetchRequest alloc] init];
	[request setEntity:entityDescription];
	
	NSPredicate *predicate = [NSPredicate predicateWithFormat:@"identityProvider = %@", identityProvider];
	[request setPredicate:predicate];
	
	NSSortDescriptor *sortDescriptor = [[NSSortDescriptor alloc] initWithKey:@"sortIndex" ascending:YES];
	[request setSortDescriptors:[NSArray arrayWithObject:sortDescriptor]];
	[sortDescriptor release];
	
	NSError *error = nil;
	NSArray *result = [context executeFetchRequest:request error:&error];
	[request release];
	
	return result;
}

+ (void)blockAllIdentitiesInManagedObjectContext:(NSManagedObjectContext *)context  {
	NSEntityDescription *entityDescription = [NSEntityDescription entityForName:@"Identity" inManagedObjectContext:context];
	NSFetchRequest *request = [[NSFetchRequest alloc] init];
	[request setEntity:entityDescription];
 
	NSError *error = nil;
	NSArray *identities = [context executeFetchRequest:request error:&error];
	[request release]; 
    
    if (error == noErr && identities != nil) {
        for (Identity *identity in identities) {
            identity.blocked = [NSNumber numberWithBool:YES];
        }
    }
}

@end