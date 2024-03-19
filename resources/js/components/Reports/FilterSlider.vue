<template>
    <div class="filter-container" @mousedown="startDrag" @mouseup="stopDrag" @mouseleave="stopDrag" ref="filterContainer">
        <button class="btn btn-sm btn-outline-secondary" v-for="filter in filters" :key="filter.id" @click="handleClick(filter, $event)">
            <font-awesome-icon icon="trash"></font-awesome-icon> {{ filter.displayName }} <span v-html="filterExtendedOverview(filter)"></span>
        </button>
    </div>
</template>

<script>
export default {

    props: {
        filters: Array,
    },

    data() {
        return {
            startX: 0,
            scrollLeft: 0,
            isDragging: false,
            preventClick: false,
        };
    },

    methods: {
        startDrag(e) {
            this.isDragging = true;
            this.startX = e.pageX - this.$refs.filterContainer.offsetLeft;
            this.scrollLeft = this.$refs.filterContainer.scrollLeft;
            this.$refs.filterContainer.classList.add('active');

            document.addEventListener('mousemove', this.mouseMoveHandler);
            document.addEventListener('mouseup', this.stopDrag);
        },
        mouseMoveHandler(e) {
            if (!this.isDragging) return;
            e.preventDefault();
            const x = e.pageX - this.$refs.filterContainer.offsetLeft;
            const walk = (x - this.startX) * 0.5; // The number 0.5 determines the scroll speed

            const distanceMoved = Math.abs(x - this.startX);
            if (distanceMoved > 2) { // Threshold for preventing click, adjust as needed
                this.preventClick = true;
            }

            this.$refs.filterContainer.scrollLeft = this.scrollLeft - walk;
        },
        stopDrag() {
            this.isDragging = false;
            this.$refs.filterContainer.classList.remove('active');

            document.removeEventListener('mousemove', this.mouseMoveHandler);
            document.removeEventListener('mouseup', this.stopDrag);

            // Reset preventClick after a delay to ensure it's set before the click event fires
            setTimeout(() => {
                this.preventClick = false;
            }, 0);
        },
        handleClick(filter, event) {
            if (this.preventClick) {
                event.preventDefault();
                return; // Do nothing if preventClick is true
            }

            // emit remove-filter event
            this.$emit('remove-filter', filter);
        },

        filterExtendedOverview(filter) {
            if(filter.selectedOperator === 'btwn') {
                return `btwn ${filter.value} <b>&</b> ${filter.valueBetween}`;
            }
            return `${filter.selectedOperator} ${filter.value}`;
        },
    }
}
</script>

<style scoped>

.filter-container {
    display: flex;
    flex-wrap: nowrap;
    overflow-x: auto;
    cursor: grab;

    button {
        flex: 0 0 auto;
        margin-right: 2px;
    }

    button:hover, button:focus, button:active {
        color: #6c757d;
        border-color: #6c757d;
        background-color: transparent;
        cursor: grabbing;
    }
}

.filter-container.active {
    cursor: grabbing; /* Change cursor appearance during drag */
}

.filter-container::-webkit-scrollbar {
    -webkit-overflow-scrolling: touch;
}

.filter-container {
    &::-webkit-scrollbar {
        display: none;
    }
}

</style>
