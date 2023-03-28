

@if ($subcategory->allSubcategories)
@php $prefix .='|--'  @endphp
    @foreach ($subcategory->allSubcategories as $subcategory)
        <option value="{{ $subcategory->id }}" > {{ $prefix }} @lang($subcategory->name)</option>
        @include('admin.partials.subcategories', ['subcategory' => $subcategory])
    @endforeach
@endif
