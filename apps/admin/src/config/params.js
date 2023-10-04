let params = {
  user: {
    statuses: {
      'active': 'Активный',
      'registered': 'Зарегистрировался',
      'suspended': 'Заблокирован'
    },
    roles: {
      'customer': 'Клиент',
      'manager': 'Менеджер',
      'designer': 'Проектировщик',
      'administrator': 'Администратор'
    },
    languages: {
      'en': 'English',
      'uk': 'Україньська',
      'ru': 'Русский'
    },
    providers: {
    }
  },
  project: {
    statuses: {
      'created': 'Создан',
      'sent': 'Отправлен',
      'returned': 'Возвращен',
      'rejected': 'Отклонен',
      'designing': 'У проектировщика',
      'specification_granted': 'Спецификация подана',
      'specification_accepted': 'Спецификация принята',
      'canceled': 'Отказ',
      'finished': 'Завершен'
    }
  }
}

export default params
