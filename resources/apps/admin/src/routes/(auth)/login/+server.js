// @ts-nocheck
import { json } from '@sveltejs/kit';
import axios from 'axios';

const API_URL = process.env.PUBLIC_API_URL || 'http://127.0.0.1:8000';

export const POST = async ({ request, cookies }) => {
	try {
		console.log('Login request received');
		const body = await request.json();
		console.log('Request body:', body);

		console.log('Making API call to:', `${API_URL}/api/auth/login`);
		try {
			const response = await axios.post(`${API_URL}/api/auth/login`, body, {
				timeout: 5000, // 5 second timeout
				headers: {
					'Accept': 'application/json',
					'Content-Type': 'application/json'
				}
			});
			console.log('API response received:', response.data);

			if (!response.data.user.is_admin) {
				console.log('Login failed - user is not admin');
				return json(
					{ success: false, message: 'you are not allowed to access this page' },
					401
				);
			}

			console.log('Setting token cookie');
			// Set the token cookie with proper options
			cookies.set('token', response.data.access_token, {
				path: '/',
				httpOnly: true,
				sameSite: 'lax',
				secure: process.env.NODE_ENV === 'production'
			});

			console.log('Login successful, returning response');
			return json({ success: true, ...response.data });
		} catch (apiError) {
			console.error('API error details:', {
				message: apiError.message,
				code: apiError.code,
				response: apiError.response?.data,
				status: apiError.response?.status
			});
			
			if (apiError.code === 'ECONNREFUSED') {
				return json({
					success: false,
					message: `Cannot connect to the backend server at ${API_URL}. Please make sure the Laravel server is running.`
				}, 503);
			}
			return json({
				success: false,
				message: apiError.response?.data?.message || 'Login failed. Please try again.'
			}, apiError.response?.status || 500);
		}
	} catch (error) {
		console.error('Server error:', error);
		return json({ 
			success: false,
			message: 'An unexpected error occurred. Please try again.' 
		}, 500);
	}
};
