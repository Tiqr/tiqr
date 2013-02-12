//
//  OCRAProtocol.h
//  Tiqr-Surfnet
//
//  Created by Ivo Jansch on 2/11/13.
//
//

#import <Foundation/Foundation.h>

@protocol OCRAProtocol <NSObject>

- (NSString *)generateOCRA:(NSString*)ocraSuite
                    secret:(NSData *)secret
                 challenge:(NSString*)challenge
                sessionKey:(NSString*)sessionKey
                     error:(NSError**)error;

@end
