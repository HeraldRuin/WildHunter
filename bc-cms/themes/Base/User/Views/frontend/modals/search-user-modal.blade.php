<div class="modal fade" id="userModal" tabindex="-1">
    <div class="modal-dialog modal-lg">
        <div class="modal-content">
            <div class="modal-body">
                <label for="changeUserInput">Найти нового заказчика по ID:</label>
                <input
                    type="text"
                    id="changeUserInput"
                    v-model="userSearchQuery"
                    class="form-control mb-2"
                    placeholder="Введите ID пользователя"
                    @input="searchUserDebounced">

                <div v-if="searchResults.length" class="mt-2">
                    <div
                        v-for="user in searchResults"
                        :key="user.id"
                        class="d-flex align-items-center justify-content-between p-2 mb-2 border rounded shadow-sm"
                        style="background-color: #f8f9fa;">
                        <div>
                            <span>ID: @{{ user.id }}</span>
                            <strong class="text-dark">@{{ user.user_name }}</strong>
                            <span>@{{ user.user_name ? '(ник)' : '(ник не задан)' }}</span>
                            <strong class="text-dark">@{{ user.first_name }} @{{ user.last_name }}</strong>
                            <br>
                        </div>
                        <button v-if="!selectedUser || selectedUser.id !== user.id" class="btn btn-sm btn-primary"
                                @click="selectUser(user)">
                            Выбрать
                        </button>
                    </div>
                </div>

                <div v-if="isSearching" class="text-muted">
                    Поиск...
                </div>
                <div v-if="noResults" class="text-danger">
                    Пользователь не найден
                </div>

                <button class="btn btn-primary mt-2" @click="saveUserChange">Сохранить</button>
            </div>
        </div>
    </div>
</div>
