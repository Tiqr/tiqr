/**
 * This file is part of the ocra-implementations package.
 *
 * More information: https://github.com/SURFnet/ocra-implementations/
 *
 * @author Ivo Jansch <ivo@egeniq.com>
 *
 * @license See the LICENSE file in the source distribution
 */
#import <Foundation/Foundation.h>

/**
 * Error codes that can occur when generating an OCRA string
 */
enum {
    OCRANumberOfDigitsTooLargeError = 100
};

@interface OCRA : NSObject {
    
}

+ (NSString *) generateOCRAForSuite:(NSString*) ocraSuite
                                key:(NSString*) key
                            counter:(NSString*) counter
                           question:(NSString*) question
                           password:(NSString*) password
                 sessionInformation:(NSString*) sessionInformation
                          timestamp:(NSString*) timeStamp
                              error:(NSError**) error;

@end
