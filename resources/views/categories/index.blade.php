@extends('layouts.app')

@section('title', 'Categories')
@section('page_title', 'Product Categories')

@section('content')
<div class="container-fluid px-0">

    <div class="d-flex justify-content-between align-items-center mb-4">
        <div>
            <h5 class="fw-bold text-dark mb-1">Product Categories</h5>
            <p class="text-muted small mb-0">Organize bags and merchandise into structured product groups</p>
        </div>
        <button class="btn btn-primary btn-sm d-flex align-items-center gap-1 shadow-sm" data-bs-toggle="modal" data-bs-target="#addCategoryModal">
            <i class="bi bi-plus-lg"></i>
            <span>Add Category</span>
        </button>
    </div>

    <div class="card card-custom">
        <div class="table-responsive">
            <table class="table table-custom mb-0">
                <thead>
                    <tr>
                        <th style="width: 60px;">#</th>
                        <th>Category Name</th>
                        <th>Slug</th>
                        <th>Description</th>
                        <th class="text-center">Associated Products</th>
                        <th class="text-end" style="width: 140px;">Actions</th>
                    </tr>
                </thead>
                <tbody>
                    @forelse($categories as $index => $category)
                        <tr>
                            <td class="text-muted">{{ $categories->firstItem() + $index }}</td>
                            <td>
                                <span class="fw-semibold text-dark">{{ $category->name }}</span>
                            </td>
                            <td><code class="text-secondary small">{{ $category->slug }}</code></td>
                            <td class="text-muted small">{{ $category->description ?? '—' }}</td>
                            <td class="text-center">
                                <span class="badge {{ $category->products_count > 0 ? 'bg-primary' : 'bg-light text-muted border' }}">
                                    {{ $category->products_count }} Products
                                </span>
                            </td>
                            <td class="text-end">
                                <div class="btn-group btn-group-sm">
                                    <button class="btn btn-outline-secondary" data-bs-toggle="modal" data-bs-target="#editModal{{ $category->id }}" title="Edit">
                                        <i class="bi bi-pencil"></i>
                                    </button>
                                    @if($category->products_count == 0)
                                        <button class="btn btn-outline-danger" onclick="confirmAction('Are you sure you want to delete category {{ $category->name }}?', 'delete-form-{{ $category->id }}')" title="Delete">
                                            <i class="bi bi-trash"></i>
                                        </button>
                                        <form id="delete-form-{{ $category->id }}" action="{{ route('categories.destroy', $category) }}" method="POST" class="d-none">
                                            @csrf
                                            @method('DELETE')
                                        </form>
                                    @else
                                        <button class="btn btn-outline-secondary" disabled title="Cannot delete: has associated products">
                                            <i class="bi bi-lock-fill"></i>
                                        </button>
                                    @endif
                                </div>
                            </td>
                        </tr>

                        <!-- Edit Modal -->
                        <div class="modal fade" id="editModal{{ $category->id }}" tabindex="-1" aria-hidden="true">
                            <div class="modal-dialog">
                                <div class="modal-content">
                                    <form action="{{ route('categories.update', $category) }}" method="POST">
                                        @csrf
                                        @method('PUT')
                                        <div class="modal-header">
                                            <h6 class="modal-title fw-bold">Edit Category: {{ $category->name }}</h6>
                                            <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                                        </div>
                                        <div class="modal-body">
                                            <div class="mb-3">
                                                <label class="form-label small fw-semibold">Category Name <span class="text-danger">*</span></label>
                                                <input type="text" name="name" class="form-control" value="{{ old('name', $category->name) }}" required>
                                            </div>
                                            <div class="mb-3">
                                                <label class="form-label small fw-semibold">Description</label>
                                                <textarea name="description" class="form-control" rows="3">{{ old('description', $category->description) }}</textarea>
                                            </div>
                                        </div>
                                        <div class="modal-footer">
                                            <button type="button" class="btn btn-light border btn-sm" data-bs-dismiss="modal">Cancel</button>
                                            <button type="submit" class="btn btn-primary btn-sm">Save Changes</button>
                                        </div>
                                    </form>
                                </div>
                            </div>
                        </div>
                    @empty
                        <tr>
                            <td colspan="6" class="text-center py-5 text-muted">
                                <i class="bi bi-tags fs-1 d-block mb-2 opacity-50"></i>
                                No categories found. Click "Add Category" to create one.
                            </td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>

        @if($categories->hasPages())
            <div class="p-3 border-top d-flex justify-content-end">
                {{ $categories->links() }}
            </div>
        @endif
    </div>

</div>

<!-- Add Category Modal -->
<div class="modal fade" id="addCategoryModal" tabindex="-1" aria-hidden="true">
    <div class="modal-dialog">
        <div class="modal-content">
            <form action="{{ route('categories.store') }}" method="POST">
                @csrf
                <div class="modal-header">
                    <h6 class="modal-title fw-bold">Create New Product Category</h6>
                    <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                </div>
                <div class="modal-body">
                    <div class="mb-3">
                        <label class="form-label small fw-semibold">Category Name <span class="text-danger">*</span></label>
                        <input type="text" name="name" class="form-control" placeholder="e.g. Backpack, Rucksack, Tote Bag" required>
                    </div>
                    <div class="mb-3">
                        <label class="form-label small fw-semibold">Description</label>
                        <textarea name="description" class="form-control" rows="3" placeholder="Optional category details or target audience..."></textarea>
                    </div>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-light border btn-sm" data-bs-dismiss="modal">Cancel</button>
                    <button type="submit" class="btn btn-primary btn-sm">Create Category</button>
                </div>
            </form>
        </div>
    </div>
</div>
@endsection
