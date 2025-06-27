document.addEventListener('DOMContentLoaded', function() {
    loadMessages();
    loadGroups();
    loadUsers();

    document.getElementById('newMessageForm').addEventListener('submit', handleNewMessage);
    document.getElementById('newGroupForm').addEventListener('submit', handleNewGroup);
});

async function loadMessages() {
    try {
        const response = await fetchWithRefresh('/api/messages');
        const data = await response.json();
        
        const messagesList = document.getElementById('messagesList');
        messagesList.innerHTML = '';
        
        data.messages.forEach(message => {
            const row = document.createElement('tr');
            row.innerHTML = `
                <td>
                    <i class="fas fa-${message.isRead ? 'envelope-open' : 'envelope'} 
                       ${message.isRead ? 'text-muted' : 'text-primary'}"></i>
                </td>
                <td class="message-link" data-id="${message.id}">${message.subject}</td>
                <td>${message.sender}</td>
                <td>${new Date(message.sentAt).toLocaleString()}</td>
            `;
            messagesList.appendChild(row);
        });

        document.querySelectorAll('.message-link').forEach(link => {
            link.addEventListener('click', () => showMessageDetail(link.dataset.id));
        });
    } catch (error) {
        console.error('Fehler beim Laden der Nachrichten:', error);
    }
}

async function loadGroups() {
    try {
        const response = await fetchWithRefresh('/api/message-groups');
        const data = await response.json();
        
        const groupsList = document.getElementById('groupsList');
        const messageGroupSelect = document.getElementById('messageGroup');
        
        groupsList.innerHTML = '';
        messageGroupSelect.innerHTML = '<option value="">Keine Gruppe</option>';
        
        data.groups.forEach(group => {
            // Füge Gruppe zur Sidebar hinzu
            const groupDiv = document.createElement('div');
            groupDiv.className = 'mb-2';
            groupDiv.innerHTML = `
                <button class="btn btn-outline-secondary w-100 text-start">
                    <i class="fas fa-users"></i> ${group.name}
                    <span class="badge bg-secondary float-end">${group.memberCount}</span>
                </button>
            `;
            groupsList.appendChild(groupDiv);

            // Füge Gruppe zum Dropdown im Nachrichtenformular hinzu
            const option = document.createElement('option');
            option.value = group.id;
            option.textContent = group.name;
            messageGroupSelect.appendChild(option);
        });
    } catch (error) {
        console.error('Fehler beim Laden der Gruppen:', error);
    }
}

async function loadUsers() {
    try {
        const response = await fetchWithRefresh('/api/users');
        if (!response.ok) {
            throw new Error('Fehler beim Laden der Benutzer');
        }
        
        const data = await response.json();
        
        // Empfänger Select
        const recipientSelect = document.getElementById('messageRecipients');
        recipientSelect.innerHTML = '';
        data.users.forEach(user => {
            const option = new Option(`${user.fullName} (${user.email})`, user.id);
            recipientSelect.appendChild(option);
        });
        
        // Gruppen-Mitglieder Select
        const groupMembersSelect = document.getElementById('groupMembers');
        groupMembersSelect.innerHTML = '';
        data.users.forEach(user => {
            const option = new Option("${user.fullName} (${user.email})", user.id);
            groupMembersSelect.appendChild(option);
        });

        // Bootstrap Select initialisieren
        $(recipientSelect).selectpicker({
            liveSearch: true,
            title: 'Empfänger auswählen...',
            size: 10,
            selectedTextFormat: 'count > 2',
            countSelectedText: '{0} Empfänger ausgewählt'
        });

        $(groupMembersSelect).selectpicker({
            liveSearch: true,
            title: 'Mitglieder auswählen...',
            size: 10,
            selectedTextFormat: 'count > 2',
            countSelectedText: '{0} Mitglieder ausgewählt'
        });

    } catch (error) {
        console.error('Fehler beim Laden der Benutzer:', error);
    }
}

function resetForm(formId, selectIds) {
    const form = document.getElementById(formId);
    form.reset();
    selectIds.forEach(id => {
        $(`#${id}`).selectpicker('deselectAll');
    });
}

async function handleNewMessage(e) {
    e.preventDefault();
    
    const messageData = {
        recipientIds: Array.from(document.getElementById('messageRecipients').selectedOptions).map(opt => opt.value),
        groupId: document.getElementById('messageGroup').value,
        subject: document.getElementById('messageSubject').value,
        content: document.getElementById('messageContent').value
    };

    try {
        const response = await fetchWithRefresh('/api/messages', {
            method: 'POST',
            body: JSON.stringify(messageData)
        });

        if (response.ok) {
            bootstrap.Modal.getInstance(document.getElementById('newMessageModal')).hide();
            resetForm('newMessageForm', ['messageRecipients', 'messageGroup']);
            loadMessages();
        }
    } catch (error) {
        console.error('Fehler beim Senden der Nachricht:', error);
    }
}

async function handleNewGroup(e) {
    e.preventDefault();
    
    const groupData = {
        name: document.getElementById('groupName').value,
        memberIds: Array.from(document.getElementById('groupMembers').selectedOptions).map(opt => opt.value)
    };

    try {
        const response = await fetchWithRefresh('/api/message-groups', {
            method: 'POST',
            body: JSON.stringify(groupData)
        });

        if (response.ok) {
            bootstrap.Modal.getInstance(document.getElementById('newGroupModal')).hide();
            resetForm('newGroupForm', ['groupMembers']);
            loadGroups();
        }
    } catch (error) {
        console.error('Fehler beim Erstellen der Gruppe:', error);
    }
}

async function showMessageDetail(messageId) {
    try {
        const response = await fetchWithRefresh(`/api/messages/${messageId}`);
        const message = await response.json();
        
        document.getElementById('messageDetailSubject').textContent = message.subject;
        document.getElementById('messageDetailSender').textContent = message.sender;
        document.getElementById('messageDetailRecipients').textContent = 
            message.recipients.map(r => r.name).join(', ');
        document.getElementById('messageDetailDate').textContent = 
            new Date(message.sentAt).toLocaleString();
        document.getElementById('messageDetailContent').textContent = message.content;
        
        const modal = new bootstrap.Modal(document.getElementById('messageDetailModal'));
        modal.show();
        
        loadMessages(); // Aktualisiere die Liste, um den Lesestatus zu aktualisieren
    } catch (error) {
        console.error('Fehler beim Laden der Nachrichtendetails:', error);
    }
}
