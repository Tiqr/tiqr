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

package org.tiqr.authenticator.security;

/**
 * Class that implements the Verhoeff Algorithm, a checksum formula for error detection
 * 
 * @see http://en.wikipedia.org/wiki/Verhoeff_algorithm
 * @author Felix De Vliegher <felix@egeniq.com>
 */
public class Verhoeff {

	// The multiplication table
	static int[][] d = new int[][] {
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

	// The permutation table
	static int[][] p = new int[][] {
		{ 0, 1, 2, 3, 4, 5, 6, 7, 8, 9 },
		{ 1, 5, 7, 6, 2, 8, 3, 0, 9, 4 },
		{ 5, 8, 0, 3, 7, 9, 6, 1, 4, 2 },
		{ 8, 9, 1, 6, 0, 4, 3, 5, 2, 7 },
		{ 9, 4, 5, 3, 1, 2, 6, 8, 7, 0 },
		{ 4, 2, 8, 6, 5, 7, 3, 9, 0, 1 },
		{ 2, 7, 9, 3, 8, 0, 6, 4, 1, 5 },
		{ 7, 0, 4, 6, 9, 1, 3, 2, 5, 8 } 
	};

	// The inverse table
	static int[] inv = { 0, 4, 3, 2, 1, 5, 6, 7, 8, 9 };

	/**
	 * For a given number generates a Verhoeff digit
	 * 
	 * @param	num		The number
	 * @return			The Verhoeff checksum digit
	 */
	public static int verhoeffDigit(String num) {

		int c = 0;
		int[] myArray = StringToReversedIntArray(num);

		for (int i = 0; i < myArray.length; i++) {
			c = d[c][p[((i + 1) % 8)][myArray[i]]];
		}

		return inv[c];
	}

	/**
	 * Validates that an entered number is Verhoeff compliant. 
	 * 
	 * NB: Make sure the check digit is the last one.
	 *
	 * @param 	num		The number
	 * @return	boolean
	 */
	public static boolean validateVerhoeff(String num) {

		int c = 0;
		int[] myArray = StringToReversedIntArray(num);

		for (int i = 0; i < myArray.length; i++) {
			c = d[c][p[(i % 8)][myArray[i]]];
		}

		return (c == 0);
	}

	/**
	 * Converts a string to a reversed integer array
	 * 
	 * @param 	num		The number
	 * @return
	 */
	private static int[] StringToReversedIntArray(String num) {

		int[] myArray = new int[num.length()];

		for (int i = 0; i < num.length(); i++) {
			myArray[i] = Integer.parseInt(num.substring(i, i + 1));
		}

		myArray = Reverse(myArray);

		return myArray;

	}

	/**
	 * Reverses an int array
	 * 
	 * @param	int[]	myArray		The array to be reversed
	 * @return	int[]
	 */
	private static int[] Reverse(int[] myArray) {
		int[] reversed = new int[myArray.length];

		for (int i = 0; i < myArray.length; i++) {
			reversed[i] = myArray[myArray.length - (i + 1)];
		}

		return reversed;
	}
}