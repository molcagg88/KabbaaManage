<script>
	// @ts-nocheck
	import { _ } from 'svelte-i18n';
	import { Paginator } from '@skeletonlabs/skeleton';
	import { getBearerToken, useApi } from '$lib/api';
	import { onMount } from 'svelte';
	import moment from 'moment';

	const api = useApi({
		Authorization: getBearerToken()
	});

	let items = [];
	let currentPage = 1;
	let loading = false;
	let totalItems = 0;
	let perPage = 15;

	let title = $_('notifications');

	const loadItems = () => {
		if (loading) return;
		loading = true;
		api.get('/notifications', {
			params: {
				page: currentPage,
				per_page: perPage
			}
		})
			.then((response) => {
				items = response.data.data;
				currentPage = response.data.current_page;
				totalItems = response.data.total;
			})
			.finally(() => (loading = false));
	};

	const markAsRead = (id) => {
		api.put(`/notifications/${id}/read`)
			.then(() => {
				items = items.map(item => {
					if (item.id === id) {
						return { ...item, read: true };
					}
					return item;
				});
			});
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
		</header>
		<div class="table-container">
			<table class="table-hover table bg-white">
				<thead>
					<tr>
						<th>Type</th>
						<th>Message</th>
						<th>Date</th>
						<th>Status</th>
						<th>Actions</th>
					</tr>
				</thead>
				<tbody>
					{#each items as item}
						<tr class={item.read ? 'opacity-50' : ''}>
							<td>
								<span class="badge {item.type === 'expiring' ? 'variant-filled-warning' : 'variant-filled-error'}">
									{item.type}
								</span>
							</td>
							<td>{item.message}</td>
							<td>{moment(item.created_at).format('LLL')}</td>
							<td>
								<span class="badge {item.read ? 'variant-filled-success' : 'variant-filled-primary'}">
									{item.read ? 'Read' : 'Unread'}
								</span>
							</td>
							<td>
								{#if !item.read}
									<button
										type="button"
										class="btn btn-sm variant-filled-primary"
										on:click={() => markAsRead(item.id)}
									>
										Mark as Read
									</button>
								{/if}
							</td>
						</tr>
					{/each}
				</tbody>
			</table>
			<div class="pt-6">
				<Paginator
					bind:settings={paginationSettings}
					showNumerals
					maxNumerals={1}
					on:amount={(event) => {
						perPage = event.detail;
						currentPage = 1;
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