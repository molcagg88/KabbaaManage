<script>
	// @ts-nocheck
	import { _ } from 'svelte-i18n';
	import { getBearerToken, getErrorMessage, useApi } from '$lib/api';
	import { useToast } from '$lib/toast';
	import { Avatar, FileButton, SlideToggle } from '@skeletonlabs/skeleton';
	import { getAvatarUrl } from '$lib/avatar';
	import { createEventDispatcher } from 'svelte';

	import CountrySelect from '$lib/components/CountrySelect.svelte';

	const toast = useToast();

	const dispatch = createEventDispatcher();

	const api = useApi({
		Authorization: getBearerToken()
	});

	export let user = { profile: {} };

	let loading = false;

	let fields = {
		name: user.name,
		phone_number: user.phone_number,
		email: user.email,
		status: user.status,
		avatar: user.avatar
	};

	const onSubmit = () => {
		loading = true;
		api.put(`/users/${user.id}`, fields)
			.then(() => {
				toast.trigger({
					message: 'Successfully updated',
					background: 'variant-filled-success'
				});
			})
			.catch((error) => {
				toast.trigger({
					message: getErrorMessage(error),
					background: 'variant-filled-error'
				});
			})
			.finally(() => (loading = false));
	};

	const onChangeFile = (event) => {
		if (event.target.files && event.target.files.length) {
			const file = event.target.files[0];
			const form = new FormData();
			form.append('avatar', file);
			api.post(`/users/${user.id}/avatar`, form)
				.then((response) => {
					user.avatar = response.data.path;
					dispatch('avatar', user.avatar);
				})
				.catch((error) => {
					toast.trigger({
						message: getErrorMessage(error),
						background: 'variant-filled-error'
					});
				})
				.finally(() => (loading = false));
		}
	};
</script>

<h3 class="h3 mb-4">{$_('memprof')}</h3>

<!-- Responsive Container (recommended) -->
<form action="" on:submit|preventDefault={onSubmit}>
	<div class="mb-6 flex items-center gap-4 pt-4">
		<Avatar
			src={getAvatarUrl(user.avatar)}
			width="w-24"
			initials={user.initial}
			rounded="rounded-full"
		/>
		<FileButton
			name="files"
			button="btn btn-sm variant-soft-primary"
			accept="image/*"
			on:change={onChangeFile}>Change Avatar</FileButton
		>
	</div>

	<div class="mb-4">
		<label class="label">
			<span>{$_('nsu')}</span>
			<input
				class="input"
				bind:value={fields.name}
				name="name"
				type="text"
				required
				disabled={loading}
			/>
		</label>
	</div>

	<div class="mb-4 flex flex-row gap-4">
		<label class="label flex-1">
			<span>Email</span>
			<input
				class="input"
				bind:value={fields.email}
				name="email"
				type="email"
				placeholder="Optional"
				disabled={loading}
			/>
		</label>
		<label class="label flex-1">
			<span>{$_('ssu')}</span>
			<select class="select" bind:value={fields.status} name="status" disabled={loading}>
				<option value=""></option>
				<option value="active">{$_('active')}</option>
				<option value="inactive">{$_('inactive')}</option>
				<option value="suspended">{$_('suspended')}</option>
			</select>
		</label>
	</div>

	<div class="mb-4">
		<label class="label">
			<span>{$_('conum')}</span>
			<input
				class="input"
				bind:value={fields.phone_number}
				name="phone_number"
				type="tel"
				required
				maxlength="12"
				pattern="[0-9]*"
				disabled={loading}
			/>
		</label>
	</div>

	<div class="flex pt-8">
		<button type="reset" class="btn variant-filled-error text-white" disabled={loading}
			>{$_('reset')}</button
		>
		<div class="flex-1"></div>
		<button type="submit" class="btn variant-filled-primary mr-2 text-white" disabled={loading}
			>{$_('sub')}</button
		>
	</div>
</form>
