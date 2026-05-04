import { Ref, ref } from 'vue';

export function useStopoverDrag<T>(list: Ref<T[]>) {
    const draggedIndex = ref<number | null>(null);
    const dropTargetIndex = ref<number | null>(null);

    function onDragStart(index: number, event: DragEvent): void {
        draggedIndex.value = index;
        if (event.dataTransfer) event.dataTransfer.effectAllowed = 'move';
    }

    function onDragOver(index: number, event: DragEvent): void {
        event.preventDefault();
        dropTargetIndex.value = index;
    }

    function onDrop(targetIndex: number): void {
        const from = draggedIndex.value;
        if (from === null || from === targetIndex) {
            draggedIndex.value = null;
            dropTargetIndex.value = null;
            return;
        }
        const [item] = list.value.splice(from, 1);
        list.value.splice(from < targetIndex ? targetIndex - 1 : targetIndex, 0, item);
        draggedIndex.value = null;
        dropTargetIndex.value = null;
    }

    function onDragEnd(): void {
        draggedIndex.value = null;
        dropTargetIndex.value = null;
    }

    return { draggedIndex, dropTargetIndex, onDragStart, onDragOver, onDrop, onDragEnd };
}
