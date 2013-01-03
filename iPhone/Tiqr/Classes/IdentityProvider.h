//
//  IdentityProvider.h
//  Tiqr
//
//  Created by Admin on 1/3/13.
//  Copyright (c) 2013 Egeniq. All rights reserved.
//

#import <Foundation/Foundation.h>
#import <CoreData/CoreData.h>

@class Identity;

@interface IdentityProvider : NSManagedObject

@property (nonatomic, retain) NSString * displayName;
@property (nonatomic, retain) NSString * infoUrl;
@property (nonatomic, retain) NSString * ocraSuite;
@property (nonatomic, retain) NSString * authenticationUrl;
@property (nonatomic, retain) NSString * identifier;
@property (nonatomic, retain) NSData * logo;
@property (nonatomic, retain) NSNumber * tiqrProtocolVersion;
@property (nonatomic, retain) NSSet *identities;
@end

@interface IdentityProvider (CoreDataGeneratedAccessors)

- (void)addIdentitiesObject:(Identity *)value;
- (void)removeIdentitiesObject:(Identity *)value;
- (void)addIdentities:(NSSet *)values;
- (void)removeIdentities:(NSSet *)values;

@end
