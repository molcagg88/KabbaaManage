<script>
	// @ts-nocheck
	import { _ } from 'svelte-i18n';
	import { Avatar, Paginator } from '@skeletonlabs/skeleton';
	import { goto } from '$app/navigation';
	import { getBearerToken, useApi } from '$lib/api';
	import { onMount } from 'svelte';

	import Status from '$lib/components/Status.svelte';
	import EditIcon from 'svelte-icons/fa/FaEdit.svelte';
	import DeleteIcon from 'svelte-icons/fa/FaTrash.svelte';
	import { getAvatarUrl } from '$lib/avatar';

	const api = useApi({
		Authorization: getBearerToken()
	});

	let items = [];
	let currentPage = 1;
	let loading = false;
	let totalItems = 0;
	let perPage = 15;
	let selectedIds = [];
	let selectAll = false;

	let title = $_('timame');

	const onDelete = (id) => {
		const confirm = window.confirm('are you sure you wanna delete this item?');
		if (confirm) {
			items = items.filter((v) => v.id != id);
			totalItems = totalItems - 1;
			api.delete(`/users/${id}`);
		}
	};

	const onBulkDelete = () => {
		if (selectedIds.length === 0) return;
		
		const confirm = window.confirm(`Are you sure you want to delete ${selectedIds.length} selected members?`);
		if (confirm) {
			api.post('/users/bulk-delete', { ids: selectedIds })
				.then(() => {
					items = items.filter((v) => !selectedIds.includes(v.id));
					totalItems = totalItems - selectedIds.length;
					selectedIds = [];
					selectAll = false;
				})
				.catch((error) => {
					alert(error.response?.data?.message || 'Error deleting members');
				});
		}
	};

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

	const onEdit = (id) => goto(`/members/${id}`);

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
				console.log(response.data);
				items = response.data.data;
				currentPage = response.data.current_page;
				totalItems = response.data.total;
				// Reset selection when loading new page
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

	// Handle selected state using a class on the root element
	$: hasSelectedItems = selectedIds.length > 0;
</script>

<svelte:head>
	<title>{title}</title>
</svelte:head>

<div class="p-4 lg:p-6" class:has-selected-items={hasSelectedItems}>
	<div class="card bg-white p-4 lg:p-6">
		<header class="card-header mb-6 flex items-center">
			<h3 class="h3">{title}</h3>
			<div class="flex-1"></div>
			<button
				type="submit"
				class="btn variant-filled-primary text-white"
				on:click={() => goto('/members/new')}
			>
				{$_('aditem')}
			</button>
		</header>
		<!-- Responsive Container (recommended) -->
		<div class="table-container">
			<!-- Native Table Element -->
			<table class="table-hover table bg-white">
				<thead>
					<tr>
						<th>
							<input
								type="checkbox"
								bind:checked={selectAll}
								on:change={onSelectAll}
								class="checkbox"
							/>
						</th>
						<th>ID</th>
						<th>{$_('nsu')}</th>
						<th>{$_('ssu')}</th>
						<th>{$_('asu')}</th>
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
							<td style="width: 100px;">{item.id}</td>
							<td>
								<div class="flex items-center gap-4">
									<Avatar
										src={item.avatar ? getAvatarUrl(item.avatar) : undefined}
										initials={item.avatar ? undefined : item.initial}
										width="w-16"
										rounded="rounded-full"
									/>
									<div class="flex flex-col">
										<a href={`/members/${item.id}`} class="font-bold"
											>{item.name}</a
										>
										<a href="mailto:{item.email}">{item.email}</a>
									</div>
								</div>
							</td>
							<td>
								<Status status={item.status} />
							</td>
							<td>
								<button
									type="button"
									class="btn-icon variant-filled-primary"
									on:click={() => onEdit(item.id)}
								>
									<span class="h-4 w-4 text-white">
										<EditIcon />
									</span>
								</button>
								<button
									type="button"
									class="btn-icon variant-filled-error"
									on:click={() => onDelete(item.id)}
								>
									<span class="h-4 w-4 text-white">
										<DeleteIcon />
									</span>
								</button>
							</td>
						</tr>
					{/each}
				</tbody>
				<tfoot>
					<tr>
						<th colspan="4" class="bg-white">Results Found {totalItems}</th>
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

{#if selectedIds.length > 0}
	<div class="fixed bottom-0 left-0 right-0 bg-white border-t border-gray-200 shadow-lg p-4">
		<div class="container mx-auto flex justify-end">
			<button
				type="button"
				class="btn variant-filled-error text-white"
				on:click={onBulkDelete}
			>
				Delete Selected ({selectedIds.length})
			</button>
		</div>
	</div>
{/if}

<style>
	.checkbox {
		@apply h-4 w-4 rounded border-gray-300 text-blue-600 focus:ring-blue-500;
	}

	/* Add padding to the bottom of the page when the delete button is visible */
	:global(body) {
		padding-bottom: 80px;
	}

	/* Remove padding when no items are selected */
	:global(body:not(.has-selected-items)) {
		padding-bottom: 0;
	}
</style>
