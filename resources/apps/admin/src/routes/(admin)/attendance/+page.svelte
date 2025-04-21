<script>
	// @ts-nocheck
	import { _ } from 'svelte-i18n';
	import { Paginator } from '@skeletonlabs/skeleton';
	import { getBearerToken, useApi } from '$lib/api';
	import { onMount } from 'svelte';
	import { Avatar } from '@skeletonlabs/skeleton';
	import { getAvatarUrl } from '$lib/avatar';
	import { useToast } from '$lib/toast';

	const api = useApi({
		Authorization: getBearerToken()
	});

	const toast = useToast();

	let items = [];
	let currentPage = 1;
	let loading = false;
	let totalItems = 0;
	let perPage = 15;
	let selectedIds = [];
	let selectAll = false;

	let title = $_('tattendance');

	const onSelectAll = () => {
		if (selectAll) {
			selectedIds = items.map(item => item.id);
		} else {
			selectedIds = [];
		}
	};

	const onSelectItem = (id) => {
		const index = selectedIds.indexOf(id);
		if (index === -1) {
			selectedIds = [...selectedIds, id];
		} else {
			selectedIds = selectedIds.filter(itemId => itemId !== id);
		}
		selectAll = selectedIds.length === items.length;
	};

	const markAttendance = () => {
		if (selectedIds.length === 0) {
			toast.trigger({
				message: $_('noselected'),
				background: 'variant-filled-warning'
			});
			return;
		}

		loading = true;
		api.post('/attendance', { user_ids: selectedIds })
			.then(() => {
				toast.trigger({
					message: $_('attendancemarked'),
					background: 'variant-filled-success'
				});
				selectedIds = [];
				selectAll = false;
				loadItems();
			})
			.catch((error) => {
				toast.trigger({
					message: error.response?.data?.message || $_('error'),
					background: 'variant-filled-error'
				});
			})
			.finally(() => (loading = false));
	};

	const loadItems = () => {
		if (loading) return;
		loading = true;
		api.get('/users', {
			params: {
				page: currentPage,
				per_page: perPage
			}
		})
			.then((response) => {
				console.log('API Response:', response.data.data);
				console.log('First user profile:', response.data.data[0]?.profile);
				items = response.data.data;
				currentPage = response.data.current_page;
				totalItems = response.data.total;
				selectedIds = [];
				selectAll = false;
			})
			.finally(() => (loading = false));
	};

	onMount(() => loadItems());

	$: paginationSettings = {
		page: currentPage - 1,
		limit: perPage,
		size: totalItems,
		amounts: [5, 10, 15, 20, 40, 60, 100]
	};
</script>

<svelte:head>
	<title>{title}</title>
</svelte:head>

<div class="p-4 lg:p-6">
	<div class="card bg-white p-4 lg:p-6">
		<header class="card-header mb-6 flex items-center">
			<h3 class="h3">{title}</h3>
			<div class="flex-1"></div>
			<button
				type="button"
				class="btn variant-filled-primary text-white"
				on:click={markAttendance}
				disabled={loading || selectedIds.length === 0}
			>
				{$_('markattendance')}
			</button>
		</header>
		<div class="table-container">
			<table class="table-hover table bg-white">
				<thead>
					<tr>
						<th>
							<input
								type="checkbox"
								checked={selectAll}
								on:change={onSelectAll}
								class="checkbox"
							/>
						</th>
						<th>ID</th>
						<th>{$_('name')}</th>
						<th>{$_('phone')}</th>
						<th>{$_('lastattendance')}</th>
					</tr>
				</thead>
				<tbody>
					{#each items as item}
						<tr>
							<td>
								<input
									type="checkbox"
									checked={selectedIds.includes(item.id)}
									on:change={() => onSelectItem(item.id)}
									class="checkbox"
								/>
							</td>
							<td>{item.id}</td>
							<td>
								<div class="flex items-center gap-4">
									<Avatar
										src={item.avatar ? getAvatarUrl(item.avatar) : undefined}
										initials={item.avatar ? undefined : item.initial}
										width="w-16"
										rounded="rounded-full"
									/>
									<div class="flex flex-col">
										<span class="font-bold">{item.name}</span>
									</div>
								</div>
							</td>
							<td>{item.phone_number || $_('nophone')}</td>
							<td>{item.last_attendance || $_('never')}</td>
						</tr>
					{/each}
				</tbody>
				<tfoot>
					<tr>
						<th colspan="4" class="bg-white">{$_('filen')} {totalItems}</th>
						<td class="bg-white"></td>
					</tr>
				</tfoot>
			</table>
			<div class="pt-6">
				<Paginator
					bind:settings={paginationSettings}
					showNumerals
					maxNumerals={1}
					on:amount={(event) => {
						perPage = event.detail;
						loadItems();
					}}
					on:page={(event) => {
						currentPage = event.detail + 1;
						loadItems();
					}}
				/>
			</div>
		</div>
	</div>
</div> 