/**
 * Khalti Payment Verification Snippet (Node.js)
 * 
 * This script demonstrates how to verify a Khalti payment token 
 * on a Node.js backend.
 * 
 * Requirements: npm install axios
 */

const axios = require('axios');

async function verifyKhaltiToken(token, amountPaisa, secretKey) {
    try {
        const response = await axios.post(
            'https://khalti.com/api/v2/payment/verify/',
            {
                token: token,
                amount: amountPaisa
            },
            {
                headers: {
                    'Authorization': `Key ${secretKey}`,
                    'Content-Type': 'application/json'
                }
            }
        );

        if (response.status === 200) {
            console.log('Verification Successful:', response.data);
            return {
                success: true,
                data: response.data
            };
        } else {
            console.error('Verification Failed:', response.data);
            return {
                success: false,
                message: 'Verification failed.'
            };
        }
    } catch (error) {
        console.error('Error during verification:', error.response ? error.response.data : error.message);
        return {
            success: false,
            message: error.message
        };
    }
}

// Example Usage:
// const KHALTI_SECRET_KEY = 'your_test_secret_key_here';
// verifyKhaltiToken('token_from_widget', 100000, KHALTI_SECRET_KEY)
//     .then(result => console.log(result));

module.exports = { verifyKhaltiToken };
