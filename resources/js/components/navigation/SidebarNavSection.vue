<template>
    <nav class="sidebar-nav-section">
        <ul>
            <li v-for="item in items" :key="item.id">
                <a :href="item.link">{{ item.name }}</a>
            </li>
        </ul>
    </nav>
</template>

<script>
export default {
    name: "SidebarNavSection",
    props: {
        items: {
            type: Array,
            required: true,
        },
    },
};
</script>

<style scoped>
.sidebar-nav-section {
    padding: 1rem;
    background-color: #f8f9fa;
}

.sidebar-nav-section ul {
    list-style: none;
    padding: 0;
    margin: 0;
}

.sidebar-nav-section li {
    margin-bottom: 0.5rem;
}

.sidebar-nav-section a {
    text-decoration: none;
    color: #007bff;
}

.sidebar-nav-section a:hover {
    text-decoration: underline;
}
</style>