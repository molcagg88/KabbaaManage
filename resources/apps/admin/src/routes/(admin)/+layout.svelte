<script>
	import AppLayout from '$lib/components/AppLayout.svelte';
	import { _ } from 'svelte-i18n';

	import './../../i18n';


	export let data = { token: '' };
</script>

<svelte:head>
	<meta charset="UTF-8" content={data.token} name="access_token" id="access_token" />
</svelte:head>

<AppLayout>
	<main>
		<slot></slot>
	</main>
</AppLayout>

<li>
	<a href="/notifications" class="flex items-center gap-2 p-2 rounded hover:bg-gray-100">
		<svg xmlns="http://www.w3.org/2000/svg" class="h-5 w-5" viewBox="0 0 20 20" fill="currentColor">
			<path d="M10 2a6 6 0 00-6 6v3.586l-.707.707A1 1 0 004 14h12a1 1 0 00.707-1.707L16 11.586V8a6 6 0 00-6-6zM10 18a3 3 0 01-3-3h6a3 3 0 01-3 3z" />
		</svg>
		<span>{$_('notifications')}</span>
	</a>
</li>
