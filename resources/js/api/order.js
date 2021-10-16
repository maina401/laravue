import request from '@/utils/request';

export function fetchList(query) {
  return request({
    url: '/orders',
    method: 'get',
    params: query,
  });
}

export function fetchOrder(id) {
  return request({
    url: '/articles/' + id,
    method: 'get',
  });
}

export function fetchPv(id) {
  return request({
    url: '/articles/' + id + '/pageviews',
    method: 'get',
  });
}

export function updateOrder(data) {
  return request({
    url: '/article/update',
    method: 'post',
    data,
  });
}

