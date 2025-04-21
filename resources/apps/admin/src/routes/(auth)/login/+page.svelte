<script>
	import { _ } from 'svelte-i18n';
	import { useToast } from '$lib/toast';
	import { useApi } from '$lib/api.js';
	import { goto } from '$app/navigation';

	const toast = useToast();
	const api = useApi();

	let fields = { email: '', password: '' };
	let loading = false;

	const onSubmit = async () => {
		if (loading) return;
		console.log('Login form submitted with fields:', { ...fields, password: '***' });
		loading = true;
		try {
			console.log('Making login request to /login');
			const response = await fetch('/login', {
				method: 'POST',
				headers: {
					'Content-Type': 'application/json'
				},
				body: JSON.stringify(fields)
			});

			console.log('Login response status:', response.status);
			const data = await response.json();
			console.log('Login response data:', { ...data, access_token: data.access_token ? '***' : undefined });

			if (!response.ok || !data.success) {
				console.error('Login failed:', data.message);
				throw new Error(data.message || $_('loginfail'));
			}

			console.log('Login successful, showing success toast');
			toast.trigger({
				message: $_('loginsuc'),
				background: 'variant-filled-success'
			});

			console.log('Setting token in meta tag');
			// Set the token in a meta tag
			const meta = document.createElement('meta');
			meta.setAttribute('name', 'access_token');
			meta.setAttribute('content', data.access_token);
			meta.setAttribute('id', 'access_token');
			document.head.appendChild(meta);

			console.log('Navigating to home page');
			// Use goto for navigation
			await goto('/');
		} catch (error) {
			console.error('Login error:', error);
			toast.trigger({
				message: error.message || $_('loginfail'),
				background: 'variant-filled-error',
				timeout: 5000 // Show error for longer
			});
		} finally {
			loading = false;
		}
	};

</script>

<div class="mb-6">
	<h3 class="h3">{$_('accountlogin')}</h3>
	<p> {$_('ban') }</p>
</div>

<form action="" on:submit|preventDefault={onSubmit}>
	<div class="mb-4">
		<label class="label">
			<span>Email</span>
			<input
				class="input"
				bind:value={fields.email}
				name="email"
				type="email"
				disabled={loading}
				placeholder="john@doe.com"
				required
			/>
		</label>
	</div>

	<div class="mb-6">
		<label class="label">
			<span>{$_('psw')}</span>
			<input
				class="input"
				bind:value={fields.password}
				name="password"
				type="password"
				placeholder=""
				disabled={loading}
				required
			/>
		</label>
	</div>
	<button
		type="submit"
		disabled={loading}
		class="btn variant-filled-primary w-full font-bold text-white">
		{loading ? 'Logging in...' : $_('loin')}
	</button>
	<a href="/forgot" class="block pt-2 text-center">{$_('fp')} </a>
</form>
