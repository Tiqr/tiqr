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

// TODO: add credits, this is based on the open source oauth_token app

#import "HOTP.h"

@implementation HOTP

@synthesize key;
@synthesize counter;
@synthesize numDigits;
@synthesize dec;
@synthesize hex;

/* Powers of ten */
static const int powers10[] = { 10, 100, 1000, 10000, 100000, 1000000, 10000000, 100000000, 1000000000, 1000000000 };
#define MAX_DIGITS_10   (sizeof(powers10) / sizeof(*powers10))
#define MAX_DIGITS_16   8

+ (HOTP *)hotpWithKey:(NSData *)key counter:(NSUInteger)counter numDigits:(int)digits {
    HOTP *hotp = [[[HOTP alloc] init] autorelease];
    hotp.key = key;
    hotp.counter = counter;
    if (digits < 1)
        digits = 1;
    else if (digits > MAX_DIGITS_10)
        digits = MAX_DIGITS_10;
    hotp.numDigits = digits;
    return hotp;
}

- (void)computePassword {
    uint8_t hash[CC_SHA1_DIGEST_LENGTH];
    uint8_t tosign[8];
    int offset;
    int value;
    int i;
    
    /* Encode counter */
    for (i = sizeof(tosign) - 1; i >= 0; i--) {
        tosign[i] = counter & 0xff;
        counter >>= 8;
    }
    
    /* Compute HMAC */
    CCHmac(kCCHmacAlgSHA1, key.bytes, key.length, tosign, sizeof(tosign), hash);
    
    /* Extract selected bytes to get 32 bit integer value */
    offset = hash[CC_SHA1_DIGEST_LENGTH - 1] & 0x0f;
    value = ((hash[offset] & 0x7f) << 24)
    | ((hash[offset + 1] & 0xff) << 16)
    | ((hash[offset + 2] & 0xff) << 8)
    | (hash[offset + 3] & 0xff);
    
    /* Generate decimal digits */
    self.dec = [NSString stringWithFormat:@"%0*d",
                (self.numDigits < MAX_DIGITS_10 ? self.numDigits : MAX_DIGITS_10),
                (self.numDigits < MAX_DIGITS_10 ? (value % powers10[self.numDigits - 1]) : value)];  
    
    /* Generate hexadecimal digits */
    self.hex = [NSString stringWithFormat:@"%0*X",
                (self.numDigits < MAX_DIGITS_16 ? self.numDigits : MAX_DIGITS_16),
                (self.numDigits < MAX_DIGITS_16 ? (value & ((1 << (4 * self.numDigits)) - 1)) : value)];
}

- (void)dealloc {
    [self.key release];
    [self.dec release];
    [self.hex release];
    [super dealloc];
}

@end