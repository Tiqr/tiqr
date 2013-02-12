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

#import "OCRA_v1.h"
#import <CommonCrypto/CommonHMAC.h>

@implementation OCRA_v1

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

+ (NSString *) generateOCRA:(NSString*) ocraSuite
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
    int ocraSuiteLength = [[ocraSuite dataUsingEncoding:NSASCIIStringEncoding] length];

    int counterLength = 0;
    int questionLength = 0;
    int passwordLength = 0;

    int sessionInformationLength = 0;
    int timeStampLength = 0;

    int hashLength = 0;

    // Default crypto algorythm
    crypto = kCCHmacAlgSHA1;
    hashLength = CC_SHA1_DIGEST_LENGTH;

    if ([ocraSuite rangeOfString: @"sha256" options: NSCaseInsensitiveSearch].location != NSNotFound) {
        crypto = kCCHmacAlgSHA256;
        hashLength = CC_SHA256_DIGEST_LENGTH;
    }
    if ([ocraSuite rangeOfString: @"sha512" options: NSCaseInsensitiveSearch].location != NSNotFound) {
        crypto = kCCHmacAlgSHA512;
        hashLength = CC_SHA512_DIGEST_LENGTH;
    }
    if ([ocraSuite rangeOfString: @"md5" options: NSCaseInsensitiveSearch].location != NSNotFound) {
        crypto = kCCHmacAlgMD5;
        hashLength = CC_MD5_DIGEST_LENGTH;
    }

    // How many digits should we return
    int indexOfFirstSemiColon = [ocraSuite rangeOfString:@":"].location;
    int indexOfLastSemiColon = [ocraSuite rangeOfString:@":" options:NSBackwardsSearch].location;
    int colonLength = indexOfLastSemiColon - indexOfFirstSemiColon;
    NSString* oS = [ocraSuite substringWithRange:NSMakeRange(indexOfFirstSemiColon, colonLength)];
    
    codeDigits = [[oS substringFromIndex:[oS rangeOfString:@"-" options:NSBackwardsSearch].location+1] intValue];

    // The codeDigits variable is used later on as an index to the powers10 array, and thus cannot be larger than 10
    if (codeDigits > 10) {
        NSString *errorTitle = NSLocalizedString(@"Server incompatible", @"Server incompatible title");
        NSString *errorMessage = NSLocalizedString(@"The server is incompatible with this version of the app.", @"Server incompatible message");
        NSDictionary *details = [NSDictionary dictionaryWithObjectsAndKeys:errorTitle, NSLocalizedDescriptionKey, errorMessage, NSLocalizedFailureReasonErrorKey, nil];
        *error = [[[NSError alloc] initWithDomain: @"org.example.tiqr.ErrorDomain" code:OCRAServerIncompatibleError userInfo:details] autorelease];
        return nil;
    }
    
    // The size of the byte array message to be encrypted
    // Counter
    if([ocraSuite rangeOfString:@":c" options:NSCaseInsensitiveSearch].location != NSNotFound) {
        // Fix the length of the HEX string
        while([counter length] < 16) {
            counter = [@"0" stringByAppendingString:counter];
        }
        counterLength=8;
    }
    // Question
    if(([ocraSuite rangeOfString:@":q" options:NSCaseInsensitiveSearch].location != NSNotFound) ||
       ([ocraSuite rangeOfString:@"-q" options:NSCaseInsensitiveSearch].location != NSNotFound)) {
        while([question length] < 256) {
            question = [question stringByAppendingString:@"0"];
        }
        questionLength=128;
    }

    // Password
    if(([ocraSuite rangeOfString:@":p" options:NSCaseInsensitiveSearch].location != NSNotFound) ||
       ([ocraSuite rangeOfString:@"-p" options:NSCaseInsensitiveSearch].location != NSNotFound)) {
        while([password length] < 40) {
            password = [@"0" stringByAppendingString:password];
        }
        passwordLength=20;
    }

    // sessionInformation
    if(([ocraSuite rangeOfString:@":s" options:NSCaseInsensitiveSearch].location != NSNotFound) ||
       ([ocraSuite rangeOfString:@":.*?:.*?\\-s" options:NSCaseInsensitiveSearch|NSRegularExpressionSearch].location != NSNotFound)) {
        while([sessionInformation length] < 128) {
            sessionInformation = [@"0" stringByAppendingString:sessionInformation];
        }
        sessionInformationLength=64;
    }
    
    // TimeStamp
    if(([ocraSuite rangeOfString:@":t" options:NSCaseInsensitiveSearch].location != NSNotFound) ||
       ([ocraSuite rangeOfString:@"-t" options:NSCaseInsensitiveSearch].location != NSNotFound)) {
        while([timeStamp length] < 16) {
            timeStamp = [@"0" stringByAppendingString:timeStamp];
        }
        timeStampLength=8;
    }

    // Remember to add "1" for the "00" byte delimiter
    int bufferSize = ocraSuiteLength +
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
    int delimiterPosition = [bArray length];
    msg[delimiterPosition] = 0x00;

    // Put the bytes of "Counter" to the message
    // Input is HEX encoded
    if(counterLength > 0 ) {
        bArray = [OCRA_v1 hexToBytes:counter];
        memcpy(msg + ocraSuiteLength + 1 , [bArray bytes], MIN(counterLength, [bArray length]));
    }

    // Put the bytes of "question" to the message
    // Input is text encoded
    if(questionLength > 0 ) {
        bArray = [OCRA_v1 hexToBytes:question];
        memcpy(msg + ocraSuiteLength + 1 + counterLength, [bArray bytes], MIN(questionLength, [bArray length]));
    }

    // Put the bytes of "password" to the message
    // Input is HEX encoded
    if(passwordLength > 0) {
        bArray = [OCRA_v1 hexToBytes:password];
        memcpy(msg + ocraSuiteLength + 1 + counterLength + questionLength, [bArray bytes], MIN(passwordLength, [bArray length]));
    }

    // Put the bytes of "sessionInformation" to the message
    // Input is text encoded
    if(sessionInformationLength > 0 ) {
        bArray = [OCRA_v1 hexToBytes:sessionInformation];
        memcpy(msg + ocraSuiteLength + 1 + counterLength + questionLength + passwordLength, [bArray bytes], MIN(sessionInformationLength, [bArray length]));
    }

    // Put the bytes of "time" to the message
    // Input is text value of minutes
    if(timeStampLength > 0) {
        bArray = [OCRA_v1 hexToBytes: timeStamp];
        memcpy(msg + ocraSuiteLength + 1 + counterLength + questionLength + passwordLength + sessionInformationLength, [bArray bytes], MIN(timeStampLength, [bArray length]));
    }

    uint8_t hash[hashLength];
   
    bArray = [OCRA_v1 hexToBytes: key];

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
