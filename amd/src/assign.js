export const modedit = () => {
    const assigneditpage = document.querySelector('#page-mod-assign-mod #region-main form');
    if (assigneditpage) {
        // Disable cmidnumber so it can't be filled in.
        assigneditpage.querySelector(`[name="cmidnumber"]`)
            .setAttribute('disabled', 'disabled');
    }
};