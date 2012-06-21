package org.tiqr.oath;

import javax.crypto.Mac;
import javax.crypto.spec.SecretKeySpec;
import java.security.MessageDigest;

import java.math.BigInteger;

import java.util.Arrays;
import java.util.Hashtable;
import java.util.regex.Pattern;
import java.util.regex.Matcher;


public class OCRA {

	private byte[] Key = null;

	private String OCRASuite = null;

	private String OCRAVersion = null;

	private String CryptoFunctionType = null;
	private String CryptoFunctionHash = null;
	private Integer CryptoFunctionHashLength = null;
	private Integer CryptoFunctionTruncation = null;

	private Boolean C = false;
	private byte[] CDataInput = null;
	private int CDataInputLength = 0;

	private Boolean Q = false;
	private String QType = "N";
	private int QLength = 8;
	private byte[] QDataInput = null;
	private int QDataInputLength = 0;

	private Boolean P = false;
	private String PType = "SHA1";
	private int PLength = 20;
	private byte[] PDataInput = null;
	private int PDataInputLength = 0;

	private Boolean S = false;
	private int SLength = 64;
	private byte[] SDataInput = null;
	private int SDataInputLength = 0;

	private Boolean T = false;
	private int TLength = 60; // 1M
	private byte[] TDataInput = null;
	private int TDataInputLength = 0;

	private Hashtable<String, Integer> TPeriods;

	private Hashtable<String, Integer> SupportedHashFunctions;


	public OCRA(String ocraSuite) throws Exception {
		init(ocraSuite);
	}


	public OCRA(String ocraSuite, String hexKey) throws Exception {
		init(ocraSuite);

		if (hexKey != null) {
			setKey(hexKey);
		}
	}


	public OCRA(String ocraSuite, String rawKey, Long counter, String question, String plainPin, String sessionInformation, Long timestamp) throws Exception {
		init(ocraSuite);

		if (rawKey != null) {
			setKey(rawKey);
		}
		if (counter != null) {
			setCounter(counter);
		}
		if (question != null) {
			setQuestion(question);
		}
		if (plainPin != null) {
			setPin(plainPin);
		}
		if (sessionInformation != null) {
			setSessionInformation(sessionInformation);
		}
		if (timestamp != null) {
			setTimestamp(timestamp);
		}
	}


	private void init(String ocraSuite) throws Exception {
		TPeriods = new Hashtable<String, Integer>(3);
		TPeriods.put("H", 3600);
		TPeriods.put("M", 60);
		TPeriods.put("S", 1);

		SupportedHashFunctions = new Hashtable<String, Integer>(3);
		SupportedHashFunctions.put("SHA1", 20);
		SupportedHashFunctions.put("SHA256", 32);
		SupportedHashFunctions.put("SHA512", 64);

		parseOCRASuite(ocraSuite);
	}


	public void setKey(String hexKey) throws Exception {
		if (hexKey == null || hexKey.length() == 0) {
			throw new Exception("Invalid key value");
		}

		setKey(hexKey, "hexstring");
	}


	public void setKey(byte[] rawKey) throws Exception {
		if (rawKey == null || rawKey.length == 0) {
			throw new Exception("Invalid key value");
		}

		Key = rawKey;
	}


	public void setKey(String key, String format) throws Exception {
		if (key == null || key.length() == 0) {
			throw new Exception("Invalid key value");
		}

		if (format == null) {
			Key = key.getBytes();
		} else if (format.equals("hexstring")) {
			Key = hexStringToBytes(key);
		} else {
			throw new Exception("Unknown input format: " + format);
		}
	}


	public void setCounter(Long counter) throws Exception {
		if (!C) {
			return;
		}

		if (counter == null || counter < 0L || counter.doubleValue() > Math.pow(2, 64)) {
			throw new Exception("Invalid counter value");
		}

		CDataInput = longToBytes(counter);
		CDataInputLength = CDataInput.length;
	}


	public void setQuestion(String question) throws Exception {
		if (question == null || question.length() > QLength) {
			throw new Exception("Invalid question value");
		}

		byte[] q_byte = null;

		if (QType.equals("A")) {
			if (!question.matches("^[A-z0-9]+$")) {
				throw new Exception("Question not alphanumeric: " + question);
			}
			q_byte = question.getBytes();
		} else if (QType.equals("H")) {
			if (!question.matches("^[a-fA-F0-9]+$")) {
				throw new Exception("Question not hexadecimal: " + question);
			}

			q_byte = hexStringToBytes(question);
		} else if (QType.equals("N")) {
			if (!question.matches("^[0-9]+$")) {
				throw new Exception("Question not numeric: " + question);
			}

			q_byte = decStringToBytes(question);
		} else {
			throw new Exception("Unknown question type: " + QType);
		}

		q_byte = nulPad(q_byte, 128, false);

		QDataInput = q_byte;
		QDataInputLength = QDataInput.length;
	}


	public void setPin(String plainPin) throws Exception {
		setPin(plainPin, null);
	}


	public void setPin(String pin, String format) throws Exception {
		if (!P) {
			return;
		}

		if (pin == null || pin.length() == 0) {
			throw new Exception("Invalid PIN value");
		}

		byte[] p_byte = null;

		if (format == null) {
			p_byte = hashFunction(PType, pin);
		} else if (format.equals("hexdigest")) {
			if (pin.length() != 2 * PLength || !pin.matches("^[a-fA-F0-9]+$")) {
				throw new Exception("Invalid PIN hexdigest value: " + pin);
			}

			p_byte = hexStringToBytes(pin);
		} else if (format.equals("digest")) {
			if (pin.length() != PLength) {
				throw new Exception("Invalid PIN digest value length: " + pin.length());
			}

			p_byte = pin.getBytes();
		} else {
			throw new Exception("Unsupported input format");
		}

		PDataInput = p_byte;
		PDataInputLength = PDataInput.length;
	}


	public void setSessionInformation(String sessionInformation) throws Exception {
		if (!S) {
			return;
		}

		if (sessionInformation == null || sessionInformation.length() != SLength) {
			throw new Exception("Invalid session information value length: " + sessionInformation.length());
		}

		SDataInput = sessionInformation.getBytes();
		SDataInputLength = SDataInput.length;
	}


	public void setTimestamp(Long timestamp) throws Exception {
		if (!T) {
			return;
		}

		if (timestamp == null || timestamp <= 0) {
			throw new Exception("Invalid timestamp value");
		}

		TDataInput = longToBytes(timestamp);
		TDataInputLength = TDataInput.length;
	}


	public void setTimestamp() throws Exception {
		setTimestamp((System.currentTimeMillis() / 1000L) / TLength);
	}


	private void parseOCRASuite(String ocraSuite) throws Exception {
		ocraSuite = ocraSuite.toUpperCase();

		OCRASuite = ocraSuite;

		String s[] = ocraSuite.split(":");
		if (s.length != 3) {
			throw new Exception("Invalid OCRA suite format: " + ocraSuite);
		}

		String algo[] = s[0].split("-");
		if (algo.length != 2) {
			throw new Exception("Invalid OCRA version: " + s[0]);
		}

		if (!algo[0].equals("OCRA")) {
			throw new Exception("Unsupported OCRA algorithm: " + algo[0]);
		}

		if (!algo[1].equals("1")) {
			throw new Exception("Unsupported OCRA version: " + algo[1]);
		}
		OCRAVersion = algo[1];

		String cf[] = s[1].split("-");
		if (cf.length != 3) {
			throw new Exception("Invalid OCRA suite crypto function: " + s[1]);
		}

		if (!cf[0].equals("HOTP")) {
			throw new Exception("Unsopported OCRA suite crypto function: " + cf[0]);
		}
		CryptoFunctionType = cf[0];

		if (!SupportedHashFunctions.containsKey(cf[1])) {
			throw new Exception("Unsopported hash function in OCRA suite crypto function: " + cf[1]);
		}
		CryptoFunctionHash = cf[1];
		CryptoFunctionHashLength = SupportedHashFunctions.get(cf[1]);

		if (!cf[2].matches("^[0-9]+$")) {
			throw new Exception("Invalid OCRA suite crypto function truncation length: " + cf[2]);
		}
		Integer trunc = Integer.parseInt(cf[2]);

		if ((trunc < 4 || trunc > 10) && trunc != 0) {
			throw new Exception("Invalid OCRA suite crypto function truncation length: " + trunc);
		}
		CryptoFunctionTruncation = trunc;

		String di[] = s[2].split("-");
		if (di.length == 0) {
			throw new Exception("Invalid OCRA suite data input: " + di);
		}

		String data_inputs = "";
		for (int i = 0; i < di.length; i++) {
			String elem = di[i];
			if (elem.length() == 0) {
				throw new Exception("Invalid OCRA suite data input");
			}

			String letter = elem.substring(0, 1);

			if (data_inputs.indexOf(letter) >= 0) {
				throw new Exception("Duplicate field in OCRA suite data input: " + elem);
			}
			data_inputs += letter;

			if (letter.equals("C") && elem.length() == 1) {
				C = true;
			} else if (letter.equals("Q")) {
				if (elem.length() == 1) {
					Q = true;
				} else if (elem.matches("^Q([AHN])([0-9]+)$")) {
					Integer q_len = Integer.parseInt(elem.substring(2));

					if (q_len < 4 || q_len > 64) {
						throw new Exception("Invalid OCRA suite data input question length: " + q_len);
					}

					Q = true;
					QType = elem.substring(1, 2);
					QLength = q_len;
				} else {
					throw new Exception("Invalid OCRA suite data input question: " + elem);
				}
			} else if (letter.equals("P")) {
				if (elem.length() == 1) {
					P = true;
				} else {
					String p_algo = elem.substring(1);
					if (!SupportedHashFunctions.containsKey(p_algo)) {
						throw new Exception("Unsupported OCRA suite PIN hash function: " + p_algo);
					}

					P = true;
					PType = p_algo;
					PLength = SupportedHashFunctions.get(p_algo);
				}
			} else if (letter.equals("S")) {
				if (elem.length() == 1) {
					S = true;
				} else if (elem.matches("^S[0-9]+$")) {
					Integer s_len = Integer.parseInt(elem.substring(1));
					if (s_len <= 0 || s_len > 512) {
						throw new Exception("Invalid OCRA suite data input session information length: " + s_len);
					}

					S = true;
					SLength = s_len;
				} else {
					throw new Exception("Invalid OCRA suite data input session information length: " + elem);
				}
			} else if (letter.equals("T")) {
				if (elem.length() == 1) {
					T = true;
				} else if (elem.matches("^T([0-9]+[HMS]){1,3}$")) {
					Pattern pat = Pattern.compile("([0-9]+)([HMS])");
					Matcher mat = pat.matcher(elem);

					Integer t_len = 0;
					String t_dupl = "";
					while (mat.find()) {
						if (t_dupl.indexOf(mat.group(2)) >= 0) {
							throw new Exception("Duplicate definitions in OCRA suite data input timestamp: " + elem);
						}

						t_len += (Integer.parseInt(mat.group(1)) * TPeriods.get(mat.group(2)));
						t_dupl += mat.group(2);
					}
					if (t_len <= 0) {
						throw new Exception("Invalid OCRA suite data input timestamp: " + elem);
					}

					T = true;
					TLength = t_len;
				} else {
					throw new Exception("Invalid OCRA suite data input timestamp: " + elem);
				}
			} else {
				throw new Exception("Unsupported OCRA suite data input field: " + elem);
			}
		}

		if (!Q) {
			throw new Exception("OCRA suite data input question not defined: " + s[2]);
		}
	}


	public String generateOCRA() throws Exception {
		if (Key == null) {
			throw new Exception("Key not defined");
		}

		if (T && TDataInput == null) {
			setTimestamp();
		}

		byte[] b_ocra = OCRASuite.concat("\0").getBytes();

		int msg_len = b_ocra.length;
		msg_len += CDataInputLength;
		msg_len += QDataInputLength;
		msg_len += PDataInputLength;
		msg_len += SDataInputLength;
		msg_len += TDataInputLength;

		byte[] msg = new byte[msg_len];

		int pos = 0;
		System.arraycopy(b_ocra, 0, msg, 0, b_ocra.length);
		pos += b_ocra.length;

		if (C) {
			if (CDataInput == null) {
				throw new Exception("Counter not defined");
			}

			System.arraycopy(CDataInput, 0, msg, pos, CDataInputLength);
			pos += CDataInputLength;
		}

		if (Q) {
			if (QDataInput == null) {
				throw new Exception("Question not defined");
			}

			System.arraycopy(QDataInput, 0, msg, pos, QDataInputLength);
			pos += QDataInputLength;
		}

		if (P) {
			if (PDataInput == null) {
				throw new Exception("PIN not defined");
			}

			System.arraycopy(PDataInput, 0, msg, pos, PDataInputLength);
			pos += PDataInputLength;
		}

		if (S) {
			if (SDataInput == null) {
				throw new Exception("Session information not defined");
			}

			System.arraycopy(SDataInput, 0, msg, pos, SDataInputLength);
			pos += SDataInputLength;
		}

		if (T) {
			if (TDataInput == null) {
				throw new Exception("Timestamp not defined");
			}

			System.arraycopy(TDataInput, 0, msg, pos, TDataInputLength);
			pos += TDataInputLength;
		}

		byte[] raw_hash = cryptoFunction(CryptoFunctionHash, Key, msg);

		if (CryptoFunctionTruncation != 0) {
			return hotpDec(raw_hash, CryptoFunctionTruncation);
		} else {
			return hotpTruncatedValue(raw_hash);
		}
	}

	private static byte[] cryptoFunction(String crypto, byte[] keyBytes, byte[] data) throws Exception {
		crypto = "Hmac" + crypto.toUpperCase();

		try {
			Mac hmac;
			hmac = Mac.getInstance(crypto);
			SecretKeySpec macKey = new SecretKeySpec(keyBytes, "RAW");
			hmac.init(macKey);

			return hmac.doFinal(data);
		} catch (Exception e) {
			throw new Exception("cryptoFunction error", e);
		}
	}


	private static byte[] hashFunction(String algo, String data) throws Exception {
		algo = algo.toUpperCase();

		try {
			MessageDigest md = MessageDigest.getInstance(algo);

			return md.digest(data.getBytes());
		} catch (Exception e) {
			throw new Exception("hashFunction error", e);
		}
	}


	private static String hotpTruncatedValue(byte[] raw) {
		int offset = raw[raw.length - 1] & 0xf;

		Integer binary =
			((raw[offset] & 0x7f) << 24) |
			((raw[offset + 1] & 0xff) << 16) |
			((raw[offset + 2] & 0xff) << 8) |
			(raw[offset + 3] & 0xff);

		return binary.toString();
	}


	public static String hotpDec(byte[] raw, Integer length) {
		String v = hotpTruncatedValue(raw);

		return v.substring(v.length() - length);
	}


	public static byte[] hexStringToBytes(String s) {
		byte[] ba = new BigInteger("10" + s, 16).toByteArray();
		byte[] ret = new byte[ba.length - 1];

		System.arraycopy(ba, 1, ret, 0, ret.length);

		return ret;
	}


	public static byte[] decStringToBytes(String s) {
		String hexStr = new BigInteger(s, 10).toString(16);

		if ((hexStr.length() % 2) == 1) {
			hexStr += "0";
		}

		byte[] ba = hexStringToBytes(hexStr);

		return ba;
	}


	public static byte[] longToBytes(long d) {
		String hex = Long.toHexString(d);

		return nulPad(hexStringToBytes(hex), 8, true);
	}


	public static byte[] nulPad(byte[] b, int n, boolean paddingLeft) {
		if (b == null) {
			 return b;
		}

		int add = n - b.length;
		if (add <= 0) {
			return b;
		}

		byte[] ret = new byte[n];
		byte[] nul = new byte[add];
		Arrays.fill(nul, (byte)'\0');

		if (paddingLeft) {
			System.arraycopy(nul, 0, ret, 0, add);
			System.arraycopy(b, 0, ret, add, b.length);
		} else {
			System.arraycopy(b, 0, ret, 0, b.length);
			System.arraycopy(nul, 0, ret, b.length, add);
		}

		return ret;
	}

}
