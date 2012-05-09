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

#import "NSString+Verhoeff.h"
#import "string.h"

// Multiplication table based on dihedral group D5 
static const int d5[10][10] = {
	{ 0, 1, 2, 3, 4, 5, 6, 7, 8, 9 },
	{ 1, 2, 3, 4, 0, 6, 7, 8, 9, 5 },
	{ 2, 3, 4, 0, 1, 7, 8, 9, 5, 6 },
	{ 3, 4, 0, 1, 2, 8, 9, 5, 6, 7 },
	{ 4, 0, 1, 2, 3, 9, 5, 6, 7, 8 },
	{ 5, 9, 8, 7, 6, 0, 4, 3, 2, 1 },
	{ 6, 5, 9, 8, 7, 1, 0, 4, 3, 2 },
	{ 7, 6, 5, 9, 8, 2, 1, 0, 4, 3 },
	{ 8, 7, 6, 5, 9, 3, 2, 1, 0, 4 },
	{ 9, 8, 7, 6, 5, 4, 3, 2, 1, 0 } 
};

// Permutation table 
static const int perm[8][10] = {
	{ 0, 1, 2, 3, 4, 5, 6, 7, 8, 9 },
	{ 1, 5, 7, 6, 2, 8, 3, 0, 9, 4 },
	{ 5, 8, 0, 3, 7, 9, 6, 1, 4, 2 },
	{ 8, 9, 1, 6, 0, 4, 3, 5, 2, 7 },
	{ 9, 4, 5, 3, 1, 2, 6, 8, 7, 0 },
	{ 4, 2, 8, 6, 5, 7, 3, 9, 0, 1 },
	{ 2, 7, 9, 3, 8, 0, 6, 4, 1, 5 },
	{ 7, 0, 4, 6, 9, 1, 3, 2, 5, 8 }
};

// Multiplicative inverse in dihedral group D5 
const int inv[10] = { 0, 4, 3, 2, 1, 5, 6, 7, 8, 9 };

@implementation NSString (Verhoeff)

- (NSUInteger)verhoeffDigit {
    const char *string = [self cStringUsingEncoding:NSASCIIStringEncoding];
    
	size_t length = strlen(string);
	int digit = 0;
	int index = 1;
    
	while (length-- > 0) {
		digit = d5[digit][perm[index][string[length] - '0']];
		index = (index + 1) % 8;
	}
    
	return inv[digit];    
}

@end