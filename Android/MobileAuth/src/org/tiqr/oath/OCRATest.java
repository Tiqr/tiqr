package org.tiqr.oath;

import org.testng.Assert;
import org.testng.annotations.DataProvider;
import org.testng.annotations.Test;

public class OCRATest {

    @Test(dataProvider = "RFCVectorsProvider")
    public void RFCVectorsTest(final String ocrasuite, final String key, final Long counter, final String question, final String pin, final String session, final Long timestamp, final String expectedResult) throws Exception {

        try {
            OCRA ocra = new OCRA(ocrasuite, key, counter, question, pin, session, timestamp);
            Assert.assertEquals(ocra.generateOCRA(), expectedResult);
        } catch (Exception e) {
            throw e;
        }
    }

    @DataProvider
    public Object[][] RFCVectorsProvider() {
        String pin = "1234";
        String pin_sha1 = "7110eda4d09e062aa5e4a390b0a572ac0d2c0220";

        String key20 = "3132333435363738393031323334353637383930";
        String key32 = "3132333435363738393031323334353637383930313233343536373839303132";
        String key64 = "31323334353637383930313233343536373839303132333435363738393031323334353637383930313233343536373839303132333435363738393031323334";

        Long timestamp = Long.parseLong("132d0b6", 16);

        String ocra1 = "OCRA-1:HOTP-SHA1-6:QN08";
        String ocra2 = "OCRA-1:HOTP-SHA256-8:C-QN08-PSHA1";
        String ocra3 = "OCRA-1:HOTP-SHA256-8:QN08-PSHA1";
        String ocra4 = "OCRA-1:HOTP-SHA512-8:C-QN08";
        String ocra5 = "OCRA-1:HOTP-SHA512-8:QN08-T1M";

        return new Object[][] { { ocra1, key20, null, "00000000", null, null, null, "237653" }, { ocra1, key20, null, "11111111", null, null, null, "243178" }, { ocra1, key20, null, "22222222", null, null, null, "653583" },
                { ocra1, key20, null, "33333333", null, null, null, "740991" }, { ocra1, key20, null, "44444444", null, null, null, "608993" }, { ocra1, key20, null, "55555555", null, null, null, "388898" },
                { ocra1, key20, null, "66666666", null, null, null, "816933" }, { ocra1, key20, null, "77777777", null, null, null, "224598" }, { ocra1, key20, null, "88888888", null, null, null, "750600" },
                { ocra1, key20, null, "99999999", null, null, null, "294470" },

                { ocra2, key32, 0L, "12345678", pin, null, null, "65347737" }, { ocra2, key32, 1L, "12345678", pin, null, null, "86775851" }, { ocra2, key32, 2L, "12345678", pin, null, null, "78192410" },
                { ocra2, key32, 3L, "12345678", pin, null, null, "71565254" }, { ocra2, key32, 4L, "12345678", pin, null, null, "10104329" }, { ocra2, key32, 5L, "12345678", pin, null, null, "65983500" },
                { ocra2, key32, 6L, "12345678", pin, null, null, "70069104" }, { ocra2, key32, 7L, "12345678", pin, null, null, "91771096" }, { ocra2, key32, 8L, "12345678", pin, null, null, "75011558" },
                { ocra2, key32, 9L, "12345678", pin, null, null, "08522129" },

                { ocra3, key32, null, "00000000", pin, null, null, "83238735" }, { ocra3, key32, null, "11111111", pin, null, null, "01501458" }, { ocra3, key32, null, "22222222", pin, null, null, "17957585" },
                { ocra3, key32, null, "33333333", pin, null, null, "86776967" }, { ocra3, key32, null, "44444444", pin, null, null, "86807031" },

                { ocra4, key64, 0L, "00000000", null, null, null, "07016083" }, { ocra4, key64, 1L, "11111111", null, null, null, "63947962" }, { ocra4, key64, 2L, "22222222", null, null, null, "70123924" },
                { ocra4, key64, 3L, "33333333", null, null, null, "25341727" }, { ocra4, key64, 4L, "44444444", null, null, null, "33203315" }, { ocra4, key64, 5L, "55555555", null, null, null, "34205738" },
                { ocra4, key64, 6L, "66666666", null, null, null, "44343969" }, { ocra4, key64, 7L, "77777777", null, null, null, "51946085" }, { ocra4, key64, 8L, "88888888", null, null, null, "20403879" },
                { ocra4, key64, 9L, "99999999", null, null, null, "31409299" },

                { ocra5, key64, null, "00000000", null, null, timestamp, "95209754" }, { ocra5, key64, null, "11111111", null, null, timestamp, "55907591" }, { ocra5, key64, null, "22222222", null, null, timestamp, "22048402" },
                { ocra5, key64, null, "33333333", null, null, timestamp, "24218844" }, { ocra5, key64, null, "44444444", null, null, timestamp, "36209546" }, };
    }

}
