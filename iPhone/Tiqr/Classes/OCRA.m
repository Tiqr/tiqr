/**
 * This file is part of the ocra-implementations package.
 *
 * More information: https://github.com/SURFnet/ocra-implementations/
 *
 * @author Ivo Jansch <ivo@egeniq.com>
 *
 * @license See the LICENSE file in the source distribution
 */

#import "OCRA.h"
#import <CommonCrypto/CommonHMAC.h>

@implementation OCRA

static const int powers10[] = { 1, 10, 100, 1000, 10000, 100000, 1000000, 10000000, 100000000, 1000000000, 1000000000 };


+(NSData*) hexToBytes: (NSString*) str {
    NSMutableData* data = [NSMutableData data];
    int idx;
    for (idx = 0; idx+2 <= [str length]; idx+=2) {
        NSRange range = NSMakeRange(idx, 2);
        NSString* hexStr = [str substringWithRange:range];
        NSScanner* scanner = [NSScanner scannerWithString:hexStr];
        unsigned int intValue;
        [scanner scanHexInt:&intValue];
        [data appendBytes:&intValue length:1];
    }
    return data;
}

+ (NSString *) generateOCRAForSuite:(NSString*) ocraSuite
                                key:(NSString*) key
                            counter:(NSString*) counter
                           question:(NSString*) question
                           password:(NSString*) password
                 sessionInformation:(NSString*) sessionInformation
                          timestamp:(NSString*) timeStamp
                              error:(NSError**) error {
    
    int codeDigits = 0;
    CCHmacAlgorithm crypto;
    NSString *result = nil;
    NSUInteger ocraSuiteLength = [[ocraSuite dataUsingEncoding:NSASCIIStringEncoding] length];
    
    int counterLength = 0;
    int questionLength = 0;
    int passwordLength = 0;
    
    int sessionInformationLength = 0;
    int timeStampLength = 0;
    
    int hashLength = 0;
    NSArray *elements = [ocraSuite componentsSeparatedByString:@":"];
    NSString *cryptoFunction = [elements objectAtIndex:1];
    NSString *dataInput = [elements objectAtIndex:2];
    
    if ([cryptoFunction rangeOfString: @"sha1" options: NSCaseInsensitiveSearch].location != NSNotFound) {
        crypto = kCCHmacAlgSHA1;
        hashLength = CC_SHA1_DIGEST_LENGTH;
    }
    if ([cryptoFunction rangeOfString: @"sha256" options: NSCaseInsensitiveSearch].location != NSNotFound) {
        crypto = kCCHmacAlgSHA256;
        hashLength = CC_SHA256_DIGEST_LENGTH;
    }
    if ([cryptoFunction rangeOfString: @"sha512" options: NSCaseInsensitiveSearch].location != NSNotFound) {
        crypto = kCCHmacAlgSHA512;
        hashLength = CC_SHA512_DIGEST_LENGTH;
    }
    
    // How many digits should we return
    codeDigits = [[cryptoFunction substringFromIndex:[cryptoFunction rangeOfString:@"-" options:NSBackwardsSearch].location+1] intValue];
    
    // The number of digits can't be larger than 10, because we'll use it as an index for the powers10 const array later on
    if (codeDigits > 10) {
        NSString *errorTitle = NSLocalizedString(@"Error", @"Error title");
        NSString *errorMessage = NSLocalizedString(@"The number of digits defined for the OTP can't be larger than 10.", @"Error message");
        NSDictionary *details = [NSDictionary dictionaryWithObjectsAndKeys:errorTitle, NSLocalizedDescriptionKey, errorMessage, NSLocalizedFailureReasonErrorKey, nil];
        *error = [[[NSError alloc] initWithDomain: @"org.example.ErrorDomain" code:OCRANumberOfDigitsTooLargeError userInfo:details] autorelease];
        return nil;
    }
    
    // The size of the byte array message to be encrypted
    // Counter
    if([dataInput rangeOfString:@"c" options:NSCaseInsensitiveSearch].location == 0) {
        // Fix the length of the HEX string
        while([counter length] < 16) {
            counter = [@"0" stringByAppendingString:counter];
        }
        counterLength=8;
    }
    // Question
    if(([dataInput rangeOfString:@"q" options:NSCaseInsensitiveSearch].location == 0) ||
       ([dataInput rangeOfString:@"-q" options:NSCaseInsensitiveSearch].location != NSNotFound)) {
        while([question length] < 256) {
            question = [question stringByAppendingString:@"0"];
        }
        questionLength=128;
    }
    
    // Password
    if([dataInput rangeOfString:@"psha1" options:NSCaseInsensitiveSearch].location != NSNotFound) {
        while([password length] < 40) {
            password = [@"0" stringByAppendingString:password];
        }
        passwordLength=20;
    }
    
    if([dataInput rangeOfString:@"psha256" options:NSCaseInsensitiveSearch].location != NSNotFound) {
        while([password length] < 64) {
            password = [@"0" stringByAppendingString:password];
        }
        passwordLength=32;
    }
    
    if([dataInput rangeOfString:@"psha512" options:NSCaseInsensitiveSearch].location != NSNotFound) {
        while([password length] < 128) {
            password = [@"0" stringByAppendingString:password];
        }
        passwordLength=64;
    }
    
    // sessionInformation
    if([dataInput rangeOfString:@"s064" options:NSCaseInsensitiveSearch].location != NSNotFound) {
        while([sessionInformation length] < 128) {
            sessionInformation = [@"0" stringByAppendingString:sessionInformation];
        }
        sessionInformationLength=64;
    } else if([dataInput rangeOfString:@"s128" options:NSCaseInsensitiveSearch].location != NSNotFound) {
        while([sessionInformation length] < 256) {
            sessionInformation = [@"0" stringByAppendingString:sessionInformation];
        }
        sessionInformationLength=128;
    } else if([dataInput rangeOfString:@"s256" options:NSCaseInsensitiveSearch].location != NSNotFound) {
        while([sessionInformation length] < 512) {
            sessionInformation = [@"0" stringByAppendingString:sessionInformation];
        }
        sessionInformationLength=256;
    } else if([dataInput rangeOfString:@"s512" options:NSCaseInsensitiveSearch].location != NSNotFound) {
        while([sessionInformation length] < 1024) {
            sessionInformation = [@"0" stringByAppendingString:sessionInformation];
        }
        sessionInformationLength=512;
    } else if ([dataInput rangeOfString:@"s" options:NSCaseInsensitiveSearch].location != NSNotFound) {
        // deviation from spec. Officially 's' without a length indicator is not in the reference implementation.
        // RFC is ambigious. However we have supported this in Tiqr since day 1, so we continue to support it.
        while([sessionInformation length] < 128) {
            sessionInformation = [@"0" stringByAppendingString:sessionInformation];
        }
        sessionInformationLength=64;
    }
    
    // TimeStamp
    if([dataInput rangeOfString:@"-t" options:NSCaseInsensitiveSearch].location != NSNotFound) {
        while([timeStamp length] < 16) {
            timeStamp = [@"0" stringByAppendingString:timeStamp];
        }
        timeStampLength=8;
    }
    
    // Remember to add "1" for the "00" byte delimiter
    NSUInteger bufferSize = ocraSuiteLength +
    counterLength +
    questionLength +
    passwordLength +
    sessionInformationLength +
    timeStampLength +
    1;
    uint8_t msg[bufferSize];
    
    
    // Put the bytes of "ocraSuite" parameters into the message
    NSData* bArray = [ocraSuite dataUsingEncoding:NSASCIIStringEncoding];
    memcpy(msg, [bArray bytes], MIN(ocraSuiteLength, [bArray length]));
    
    // Delimiter
    NSUInteger delimiterPosition = [bArray length];
    msg[delimiterPosition] = 0x00;
    
    // Put the bytes of "Counter" to the message
    // Input is HEX encoded
    if(counterLength > 0 ) {
        bArray = [OCRA hexToBytes:counter];
        memcpy(msg + ocraSuiteLength + 1 , [bArray bytes], MIN(counterLength, [bArray length]));
    }
    
    // Put the bytes of "question" to the message
    // Input is text encoded
    if(questionLength > 0 ) {
        bArray = [OCRA hexToBytes:question];
        memcpy(msg + ocraSuiteLength + 1 + counterLength, [bArray bytes], MIN(questionLength, [bArray length]));
    }
    
    // Put the bytes of "password" to the message
    // Input is HEX encoded
    if(passwordLength > 0) {
        bArray = [OCRA hexToBytes:password];
        memcpy(msg + ocraSuiteLength + 1 + counterLength + questionLength, [bArray bytes], MIN(passwordLength, [bArray length]));
    }
    
    // Put the bytes of "sessionInformation" to the message
    // Input is text encoded
    if(sessionInformationLength > 0 ) {
        bArray = [OCRA hexToBytes:sessionInformation];
        memcpy(msg + ocraSuiteLength + 1 + counterLength + questionLength + passwordLength, [bArray bytes], MIN(sessionInformationLength, [bArray length]));
    }
    
    // Put the bytes of "time" to the message
    // Input is text value of minutes
    if(timeStampLength > 0) {
        bArray = [OCRA hexToBytes: timeStamp];
        memcpy(msg + ocraSuiteLength + 1 + counterLength + questionLength + passwordLength + sessionInformationLength, [bArray bytes], MIN(timeStampLength, [bArray length]));
    }
    
    uint8_t hash[hashLength];
    
    bArray = [OCRA hexToBytes: key];
    
    CCHmac(crypto, [bArray bytes], [bArray length], msg, sizeof(msg), hash);
    
    /* Extract selected bytes to get 32 bit integer value */
    int offset = hash[hashLength - 1] & 0x0f;
    
    int binary = ((hash[offset] & 0x7f) << 24)
    | ((hash[offset + 1] & 0xff) << 16)
    | ((hash[offset + 2] & 0xff) << 8)
    | (hash[offset + 3] & 0xff);
    
    /* Generate decimal digits */
    int decimalResult = (binary % powers10[codeDigits]);
    result = [NSString stringWithFormat:@"%d", decimalResult];
    
    while ([result length] < codeDigits) {
        result = [@"0" stringByAppendingString: result];
    }
    return result;
}
@end
