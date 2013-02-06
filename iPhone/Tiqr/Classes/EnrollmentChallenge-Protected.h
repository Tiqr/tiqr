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

#import "EnrollmentChallenge.h"
#import "Challenge-Protected.h"

@interface EnrollmentChallenge ()

@property (nonatomic, copy) NSString *identityProviderIdentifier;
@property (nonatomic, copy) NSString *identityProviderDisplayName;
@property (nonatomic, copy) NSString *identityProviderAuthenticationUrl;
@property (nonatomic, copy) NSString *identityProviderInfoUrl;
@property (nonatomic, copy) NSString *identityProviderOcraSuite;
@property (nonatomic, copy) NSData *identityProviderLogo;
@property (nonatomic, copy) NSNumber *identityProviderTiqrProtocolVersion;

@property (nonatomic, copy) NSString *identityIdentifier;
@property (nonatomic, copy) NSString *identityDisplayName;

@property (nonatomic, copy) NSString *enrollmentUrl;
@property (nonatomic, copy) NSString *returnUrl;

@end