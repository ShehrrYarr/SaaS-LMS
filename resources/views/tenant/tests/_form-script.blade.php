{{-- Shared Alpine component for the test/panel builder (used by create + edit) --}}
<script>
function testForm(config) {
    return {
        availableTests: config.availableTests,
        availablePanels: config.availablePanels || [],
        isPanel: config.initialIsPanel,
        resultType: config.initialResultType,
        newCategory: false,
        testSearch: '',
        newHeader: '',
        _key: 0,
        layout: [],

        init() {
            this.layout = (config.initialLayout || []).map(r => ({ ...r, _k: this.nextKey() }));
        },

        get filteredTests() {
            const q = this.testSearch.trim().toLowerCase();
            if (!q) return this.availableTests;
            return this.availableTests.filter(t =>
                t.name.toLowerCase().includes(q) ||
                (t.code || '').toLowerCase().includes(q) ||
                (t.category || '').toLowerCase().includes(q));
        },
        get filteredPanels() {
            const q = this.testSearch.trim().toLowerCase();
            if (!q) return this.availablePanels;
            return this.availablePanels.filter(p =>
                p.name.toLowerCase().includes(q) ||
                (p.code || '').toLowerCase().includes(q) ||
                (p.category || '').toLowerCase().includes(q));
        },
        nextKey() { return 'k' + (++this._key); },
        addHeader() {
            const label = this.newHeader.trim();
            this.layout.push({ type: 'header', label: label, _k: this.nextKey() });
            this.newHeader = '';
        },
        addTest(id) {
            if (this.hasTest(id)) return;
            this.layout.push({ type: 'test', id: id, _k: this.nextKey() });
        },
        hasTest(id) { return this.layout.some(r => r.type === 'test' && r.id === id); },
        addPanel(id) {
            if (this.hasPanel(id)) return;
            this.layout.push({ type: 'panel', id: id, _k: this.nextKey() });
        },
        hasPanel(id) { return this.layout.some(r => r.type === 'panel' && r.id === id); },
        removeItem(i) { this.layout.splice(i, 1); },
        moveUp(i) { if (i > 0) { const a = this.layout; [a[i-1], a[i]] = [a[i], a[i-1]]; } },
        moveDown(i) { const a = this.layout; if (i < a.length - 1) { [a[i+1], a[i]] = [a[i], a[i+1]]; } },
        testName(id) { const t = this.availableTests.find(t => t.id === id); return t ? t.name : '(deleted test)'; },
        testMeta(id) {
            const t = this.availableTests.find(t => t.id === id);
            if (!t) return '';
            if (t.result_type === 'text') return 'text';
            return [t.unit, parseFloat(t.price).toFixed(2)].filter(Boolean).join(' · ');
        },
        panelName(id) { const p = this.availablePanels.find(p => p.id === id); return p ? p.name : '(unavailable panel)'; },
        panelMeta(id) {
            const p = this.availablePanels.find(p => p.id === id);
            return p ? ('Panel · ' + p.tests_count + ' tests') : '';
        }
    };
}
</script>
